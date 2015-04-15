<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\Adapter\AdapterBase.
 */

namespace Drupal\facetapi\Adapter;

use Drupal\Core\Plugin\PluginBase;
use Drupal\facetapi\Adapter\AdapterInterface;

/**
 * Base class for Facet API adapters.
 *
 * @TODO: rewrite D7 comment block:
 * Adapters are responsible for abstracting interactions with the Search backend
 * that are necessary for faceted search. The adapter is also responsible for
 * retrieving facet information passed by the user via the a processor plugin
 * taking the appropriate action, whether it is checking dependencies for all
 * enabled facets or passing the appropriate query type plugin to the backend
 * so that it can execute the actual facet query.
 */
abstract class AdapterBase extends PluginBase implements AdapterInterface {

  /**
   * The search keys, or query text, submitted by the user.
   *
   * @var string
   */
  protected $keys;

  /**
   * An array of FacetapiFacet objects for facets being rendered.
   *
   * @var array
   *
   * @see FacetapiFacet
   */
  protected $facets = array();

  /**
   * An array of FacetapiFacetProcessor objects.
   *
   * @var array
   *
   * @see FacetapiFacetProcessor
   * @see FacetapiAdapter::processFacets()
   */
  protected $processors = array();

  /**
   * An array of executed query type plugins keyed by field name.
   *
   * @var array
   *
   * @see FacetapiQueryTypeInterface
   */
  protected $queryTypes = array();

  /**
   * @TODO: generalize to ProcessorInterface and properly type hint in __construct().
   * The url processor plugin associated with this adapter.
   *
   * @var FacetapiUrlProcessor
   *
   * @see FacetapiUrlProcessor
   */
  protected $urlProcessor;

  /**
   * An array of active items created by FacetapiAdapter::processActiveItems().
   *
   * In order to retrieve data efficiently, the active items are stored in two
   * ways. The "filter" key is an associative array of active items keyed by
   * the raw filter passed through the source, usually in field:value format.
   * The "facet" key is a multidimensional array where the second dimension is
   * keyed by the machine name of the facet and the third dimension is an array
   * of active items keyed by the facet value.
   *
   * The active items are associative arrays containing (but not limited to):
   * - field alias: The facet alias defined in the facet definition.
   * - value: The active value passed through the source (usually $_GET) to
   *   filter the result set.
   * - pos: The zero-based position of the value in the source data. The url
   *   processor plugin uses the "pos" to efficiently remove certain values when
   *   building query strings in FacetapiQueryTypeInterface::getQueryString().
   *
   * Additional keys may be added to this array via the query type plugin's
   * FacetapiQueryTypeInterface::extract() method. For example, date and range
   * query types add the "start" and "end" values of the range.
   *
   * @var array
   *
   * @see FacetapiAdapter::processActiveItems()
   */
  protected $activeItems;

  /**
   * A boolean flagging whether the facets have been processed, or built.
   *
   * This variable acts as a per-adapter semaphore that ensures facet data is
   * processed only once.
   *
   * @var boolean
   *
   * @see FacetapiAdapter::processFacets()
   */
  protected $processed = FALSE;

  /**
   * Stores the search path associated with this searcher.
   *
   * @var string
   */
  protected $searchPath;

  /**
   * Stores settings with defaults.
   *
   * @var array
   *
   * @see FacetapiAdapter::getFacetSettings()
   */
  protected $settings = array();

  public function __construct() {
    // TODO: create constructor.
  }

  /**
   * Returns the search path associated with this searcher.
   *
   * @return string
   *   A string containing the search path.
   *
   * @todo D8 should provide an API function for this.
   */
  public function getSearchPath() {
    // TODO: Implement getSearchPath() method.
  }

  /**
   * Sets the search keys, or query text, submitted by the user.
   *
   * @param string $keys
   *   The search keys, or query text, submitted by the user.
   *
   * @return FacetapiAdapter
   *   An instance of this class.
   */
  public function setSearchKeys($keys) {
    $this->keys = $keys;
    return $this;
  }

  /**
   * Gets the search keys, or query text, submitted by the user.
   *
   * @return string
   *   The search keys, or query text, submitted by the user.
   */
  public function getSearchKeys() {
    return $this->keys;
  }

  /**
   * Returns the number of results returned by the search query.
   *
   * @return int
   *   The number of results returned by the search query.
   */
  public function getResultCount() {
    // TODO: Implement getResultCount() method.
  }

  /**
   * Returns the number of results per page.
   *
   * @return int
   *   The number of results per page, or the limit.
   */
  public function getPageLimit() {
    // TODO: Implement getPageLimit() method.
  }

  /**
   * Returns the page number of the search result set.
   *
   * @return int
   *   The current page of the result set.
   */
  public function getPageNumber() {
    // TODO: Implement getPageNumber() method.
  }

  /**
   * Returns the total number of pages in the result set.
   *
   * @return int
   *   The total number of pages.
   */
  public function getPageTotal() {
    // TODO: Implement getPageTotal() method.
  }

  /**
   * Allows the backend to add facet queries to its native query object.
   *
   * This method is called by the implementing module to initialize the facet
   * display process. The following actions are taken:
   * - FacetapiAdapter::initActiveFilters() hook is invoked.
   * - Dependency plugins are instantiated and executed.
   * - Query type plugins are executed.
   *
   * @param mixed $query
   *   The backend's native query object.
   *
   * @todo Should this method be deprecated in favor of one name init()? This
   *   might make the code more readable in implementing modules.
   *
   * @see FacetapiAdapter::initActiveFilters()
   */
  function addActiveFilters($query) {
    // TODO: Implement addActiveFilters() method.
  }

  /**
   * Hook that allows the backend to initialize its query object for faceting.
   *
   * @param mixed $query
   *   The backend's native object.
   */
  public function initActiveFilters($query) {
    // TODO: Implement initActiveFilters() method.
  }

  /**
   * Returns enabled facets for the searcher associated with this adapter.
   *
   * @return array
   *   An array of enabled facets.
   */
  public function getEnabledFacets() {
    // TODO: Implement getEnabledFacets() method.
  }

  /**
   * Returns a FacetapiFacet instance for the facet being rendered.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   *
   * @return FacetapiFacet
   *   The facet rendering object object.
   */
  public function getFacet(array $facet) {
    // TODO: Implement getFacet() method.
  }

  /**
   * Returns the facet's instantiated query type plugin.
   *
   * @param array|string $facet
   *   Either the facet definition as returned by facetapi_facet_load() or the
   *   machine readable name of the facet.
   *
   * @return FacetapiQueryTypeInterface|NULL
   *   The instantiated query type plugin, NULL if the passed facet is not valid
   *   or does not have a query type plugin associated with it.
   */
  public function getFacetQuery($facet) {
    // TODO: Implement getFacetQuery() method.
  }

  /**
   * Maps a facet's index value to a human readable value displayed to the user.
   *
   * @param string $facet_name
   *   The machine readable name of the facet.
   * @param string $value
   *   The raw value passed through the query string.
   *
   * @return string
   *   The mapped value.
   */
  public function getMappedValue($facet_name, $value) {
    // TODO: Implement getMappedValue() method.
  }

  /**
   * Returns the processor associated with the facet.
   *
   * @param string $facet_name
   *   The machine readable name of the facet.
   *
   * @return FacetapiFacetProcessor|FALSE
   *   The instantiated processor object, FALSE if the passed facet is not valid
   *   or does not have processor instantiated for it.
   */
  public function getProcessor($facet_name) {
    // TODO: Implement getProcessor() method.
  }

  /**
   * Helper function that returns the query string variables for a facet item.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not.
   *
   * @return array
   *   The query string vriables.
   *
   * @see FacetapiUrlProcessor::getQueryString()
   */
  public function getQueryString(array $facet, array $values, $active) {
    // TODO: Implement getQueryString() method.
  }

  /**
   * Helper function that returns the path for a facet link.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not.
   *
   * @return string
   *   The facet path.
   *
   * @see FacetapiUrlProcessor::getFacetPath()
   */
  public function getFacetPath(array $facet, array $values, $active) {
    // TODO: Implement getFacetPath() method.
  }

  /**
   * Initializes facet builds, sets the breadcrumb trail.
   *
   * Facets are built via FacetapiFacetProcessor objects. Facets only need to be
   * processed, or built, once regardless of how many realms they are rendered
   * in. The FacetapiAdapter::processed semaphore is set when this method is
   * called ensuring that facets are built only once regardless of how many
   * times this method is called.
   *
   * @todo For clarity, should this method be named buildFacets()?
   */
  public function processFacets() {
    // TODO: Implement processFacets() method.
  }
}
