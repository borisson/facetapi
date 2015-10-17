<?php

/**
 * @file
 * Contains \Drupal\facetapi\Tests\IntegrationTest.
 */

namespace Drupal\facetapi\Tests;

use \Drupal\facetapi\Tests\WebTestBase as FacetWebTestBase;
use Drupal\search_api\Entity\Index;

/**
 * Tests the overall functionality of the Facet API admin UI.
 *
 * @group facetapi
 */
class IntegrationTest extends FacetWebTestBase {

  /**
   * @var Index $index
   *   A search api index.
   */
  protected $index;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests the facet api permissions.
   */
  public function testOverviewPermissions() {
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');

    $this->drupalLogin($this->unauthorizedUser);
    $this->drupalGet($facet_overview);
    $this->assertResponse(403);
    $this->assertText('Access denied');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
  }

  /**
   * Tests various operations via the Facet API's admin UI.
   */
  public function testFramework() {
    $this->drupalLogin($this->adminUser);

    // Create search api test server & index.
    $this->getTestServer();
    $this->index = $this->getTestIndex();

    $this->addFieldsToIndex();

    $this->checkEmptyOverview();
    $this->addFacet("Test Facet name");
    $this->editFacet("Test Facet name");

    $this->deleteUnusedFacet("Test Facet name");
  }

  /**
   * Get the facet overview page and make sure no other facets have been defined
   * yet.
   */
  protected function checkEmptyOverview() {
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);

    // The list overview has Field: field_name, Widget: widget_name as
    // description. This tests on the absence of that.
    $this->assertNoText('Widget:');
    $this->assertNoText('Field:');
  }

  /**
   * Tests adding a facet trough the interface.
   *
   * @param $facet_name
   */
  protected function addFacet($facet_name) {
    $facet_id = preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));

    // Go to the Add facet page and make sure that returns a 200
    $facet_add_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.add_form', [], ['absolute' => TRUE]);
    $this->drupalGet($facet_add_page);
    $this->assertResponse(200);

    $form_values = [
      'name' => '',
      'id' => $facet_id,
      'widget' => 'links',
      'status' => 1,
    ];

    // Try filling out the form, but without having filled in a name for the
    // facet to test for form errors.
    $this->drupalPostForm($facet_add_page, $form_values, $this->t('Save'));
    $this->assertText($this->t('Facet name field is required.'));
    $this->assertText($this->t('Facet source field is required.'));

    // Make sure that when filling out the name, the form error disappears.
    $form_values['name'] = $facet_name;
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertNoText($this->t('Facet name field is required.'));

    // Configure the facet source by selecting one of the search api views.
    $this->drupalGet($facet_add_page);
    $this->drupalPostForm(NULL, ['facet_source' => 'search_api_views:search_api_test_views_fulltext:page_1'], $this->t('Configure facet source'));

    // @todo TEMPORARY FIX FOR https://www.drupal.org/node/2593611
    $this->drupalPostForm(NULL, ['widget' => 'links'], $this->t('Configure widget'));

    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertText($this->t('Facet field field is required.'));

    $facet_source_form = [
      'facet_source' => 'search_api_views:search_api_test_views_fulltext:page_1',
      'facet_source_configs[search_api_views:search_api_test_views_fulltext:page_1][field_identifier]' => 'entity:entity_test/type',
    ];
    $this->drupalPostForm(NULL, $form_values + $facet_source_form, $this->t('Save'));
    $this->assertNoText('field is required.');

    // Make sure that the redirection back to the overview was successful and
    // the newly added facet is shown on the overview page.
    $this->assertText($this->t('The facet was successfully saved.'));
    $this->assertUrl($this->urlGenerator->generateFromRoute('facetapi.overview'), [], 'Correct redirect to index page.');
    $this->assertText($facet_name);
  }

  /**
   * Tests editing of a facet through the UI.
   *
   * @param $facet_name
   */
  public function editFacet($facet_name) {
    $facet_id = preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));

    $facet_edit_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.edit_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet edit page and make sure "edit facet %facet" is present.
    $this->drupalGet($facet_edit_page);
    $this->assertResponse(200);
    $this->assertRaw($this->t('Edit facet %facet', ['%facet' => $facet_name]));

    // Change the facet name to add in "-2" to test editing of a facet works.
    $form_values = ['name' => $facet_name . ' - 2'];
    $this->drupalPostForm($facet_edit_page, $form_values, $this->t('Save'));

    // Make sure that the redirection back to the overview was successful and
    // the edited facet is shown on the overview page.
    $this->assertText($this->t('The facet was successfully saved.'));
    $this->assertUrl($this->urlGenerator->generateFromRoute('facetapi.overview'), [], 'Correct redirect to index page.');
    $this->assertText($facet_name);

    // Make sure the "-2" suffix is still on the facet when editing a facet.
    $this->drupalGet($facet_edit_page);
    $this->assertRaw($this->t('Edit facet %facet', ['%facet' => $facet_name . ' - 2']));

    // Edit the form and change the facet's name back to the initial name.
    $form_values = ['name' => $facet_name];
    $this->drupalPostForm($facet_edit_page, $form_values, $this->t('Save'));

    // Make sure that the redirection back to the overview was successful and
    // the edited facet is shown on the overview page.
    $this->assertText($this->t('The facet was successfully saved.'));
    $this->assertUrl($this->urlGenerator->generateFromRoute('facetapi.overview'), [], 'Correct redirect to index page.');
    $this->assertText($facet_name);
  }

  /**
   * This deletes an unused facet through the UI.
   *
   * @param string $facet_name
   */
  protected function deleteUnusedFacet($facet_name) {
    $facet_id = preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));

    $facet_delete_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.delete_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet delete page and make the warning is shown.
    $this->drupalGet($facet_delete_page);
    $this->assertResponse(200);
    $this->assertText($this->t('Are you sure you want to delete the facet'));

    // Actually submit the confirmation form.
    $this->drupalPostForm(NULL, [], $this->t('Delete'));

    // Check that the facet by testing for the message and the absence of the
    // facet name on the overview.
    $this->assertRaw($this->t('The facet %facet has been deleted.', ['%facet' => $facet_name]));

    // Refresh the page because on the previous page the $facet_name is still
    // visible (in the message).
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
    $this->assertNoText($facet_name);
  }

  protected function addFieldsToIndex() {
    $edit = array(
      'fields[entity:node/nid][indexed]' => 1,
      'fields[entity:node/title][indexed]' => 1,
      'fields[entity:node/title][type]' => 'text',
      'fields[entity:node/title][boost]' => '21.0',
      'fields[entity:node/body][indexed]' => 1,
      'fields[entity:node/uid][indexed]' => 1,
      'fields[entity:node/uid][type]' => 'search_api_test_data_type',
    );

    $this->drupalPostForm('admin/config/search/search-api/index/webtest_index/fields', $edit, $this->t('Save changes'));
    $this->assertText($this->t('The changes were successfully saved.'));
  }

}
