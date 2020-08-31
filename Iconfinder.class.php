<?php

    // Namespace overhead
    namespace onassar\Iconfinder;
    use onassar\RemoteRequests;

    /**
     * Iconfinder
     * 
     * PHP wrapper for Iconfinder.
     * 
     * @link    https://github.com/getstencil/PHP-Iconfinder
     * @author  Oliver Nassar <oliver@getstencil.com>
     * @extends RemoteRequests\Base
     */
    class Iconfinder extends RemoteRequests\Base
    {
        /**
         * RemoteRequets\Pagination
         * 
         */
        use RemoteRequests\Pagination;

        /**
         * RemoteRequets\RateLimits
         * 
         */
        use RemoteRequests\RateLimits;

        /**
         * RemoteRequets\SearchAPI
         * 
         */
        use RemoteRequests\SearchAPI;

        /**
         * _apiId
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_apiId = null;

        /**
         * _apiSecret
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_apiSecret = null;

        /**
         * _host
         * 
         * @access  protected
         * @var     string (default: 'api.iconfinder.com')
         */
        protected $_host = 'api.iconfinder.com';

        /**
         * _paths
         * 
         * @access  protected
         * @var     array
         */
        protected $_paths = array(
            'search' => '/v3/icons/search'
        );

        /**
         * __construct
         * 
         * @access  public
         * @return  void
         */
        public function __construct()
        {
            $this->_maxResultsPerPage = 100;
            // $this->_maxResultsPerPage = 16;
            $this->_responseResultsIndex = 'icons';
        }

        /**
         * _formatSearchResults
         * 
         * @access  protected
         * @param   array $results
         * @param   string $query
         * @return  array
         */
        protected function _formatSearchResults(array $results, string $query): array
        {
            $results = $this->_includeOriginalQuery($results, $query);
            $results = $this->_normalizeSearchResults($results);
            return $results;
        }

        /**
         * _getAuthRequestData
         * 
         * @access  protected
         * @return  array
         */
        protected function _getAuthRequestData(): array
        {
            $authRequestData = array(
                'client_id' => $this->_apiId,
                'client_secret' => $this->_apiSecret
            );
            return $authRequestData;
        }

        /**
         * _getPaginationRequestData
         * 
         * @access  protected
         * @return  array
         */
        protected function _getPaginationRequestData(): array
        {
            $count = $this->_limit;
            $offset = $this->_offset;
            $paginationRequestData = compact('count', 'offset');
            return $paginationRequestData;
        }

        /**
         * _getPathRequestURL
         * 
         * @access  protected
         * @param   string $path
         * @return  string
         */
        protected function _getPathRequestURL(string $path): string
        {
            $host = $this->_host;
            $url = 'https://' . ($host) . ($path);
            return $url;
        }

        /**
         * _getRateLimitResetValue
         * 
         * Iconfinder has the reset set as a date time value rather than a
         * timestamp.
         * 
         * @access  protected
         * @param   
         * @return  null|int|string
         */
        protected function _getRateLimitResetValue()
        {
            $reset = $this->_getRateLimitProperty('X-Ratelimit-Reset');
            $reset = strtotime($reset);
            return $reset;
        }

        /**
         * _getSearchQueryRequestData
         * 
         * @access  protected
         * @param   string $query
         * @return  array
         */
        protected function _getSearchQueryRequestData(string $query): array
        {
            $premium = 0;
            $vector = 1;
            $nocache = $this->_getRandomString(8);
            $args = array('query', 'premium', 'vector', 'nocache');
            $queryRequestData = compact(... $args);
            return $queryRequestData;
        }

        /**
         * _getSearchResultURLs
         * 
         * @access  protected
         * @param   array $result
         * @return  null|array
         */
        protected function _getSearchResultURLs(array $result): ?array
        {
            $bitmap = false;
            $vector = false;
            foreach ($result['raster_sizes'] as $size) {
                if ((int) $size['size_width'] === 128) {
                    $bitmap = $size['formats'][0]['preview_url'];
                    break;
                }
            }
            if ($bitmap === false) {
                return null;
            }
            foreach ($result['vector_sizes'] as $size) {
                foreach ($size['formats'] as $format) {
                    if ($format['format'] === 'svg') {
                        $vector = $format['download_url'];
                        $vector = 'https://api.iconfinder.com/v3' . ($vector);
                        break;
                    }
                }
            }
            if ($vector === false) {
                return null;
            }
            $urls = array(
                'svg' => $vector,
                'png' => array(
                    '128' => $bitmap
                )
            );
            return $urls;
        }

        /**
         * _normalizeSearchResults
         * 
         * @access  protected
         * @param   array $results
         * @return  array
         */
        protected function _normalizeSearchResults(array $results): array
        {
            foreach ($results as $index => $result) {
                if (isset($result['icon_id']) === false) {
                    unset($results[$index]);
                    continue;
                }
                if (isset($result['tags']) === false) {
                    unset($results[$index]);
                    continue;
                }
                if (isset($result['raster_sizes']) === false) {
                    unset($results[$index]);
                    continue;
                }
                if (isset($result['vector_sizes']) === false) {
                    unset($results[$index]);
                    continue;
                }
                $urls = $this->_getSearchResultURLs($result);
                if ($urls === null) {
                    unset($results[$index]);
                    continue;
                }
                $styles = array();
                if (isset($result['styles']) === true) {
                    foreach ($result['styles'] as $style) {
                        array_push($styles, $style['identifier']);
                    }
                }
                $results[$index] = array(
                    'id' => $result['icon_id'],
                    'tags' => $result['tags'],
                    'color' => (int) $result['is_icon_glyph'] === 0,
                    'styles' => $styles,
                    'urls' => $urls
                );
            }
            return $results;
        }

        /**
         * _setPathRequestData
         * 
         * @access  protected
         * @return  void
         */
        protected function _setPathRequestData(): void
        {
            $authRequestData = $this->_getAuthRequestData();
            $this->mergeRequestData($authRequestData);
        }

        /**
         * _setPathRequestURL
         * 
         * @access  protected
         * @param   string $path
         * @return  void
         */
        protected function _setPathRequestURL(string $path): void
        {
            $pathURL = $this->_getPathRequestURL($path);
            $this->setURL($pathURL);
        }

        /**
         * getPath
         * 
         * Calls parent::_getURLResponse to bypass the json expected response
         * type setting.
         * 
         * @access  public
         * @param   string $path
         * @return  null|string
         */
        public function getPath(string $path): ?string
        {
            $this->_setPathRequestData();
            $this->_setPathRequestURL($path);
            $response = parent::_getURLResponse() ?? null;
            return $response;
        }

        /**
         * setAPIId
         * 
         * @access  public
         * @param   string $apiId
         * @return  void
         */
        public function setAPIId(string $apiId): void
        {
            $this->_apiId = $apiId;
        }

        /**
         * setAPISecret
         * 
         * @access  public
         * @param   string $apiSecret
         * @return  void
         */
        public function setAPISecret(string $apiSecret): void
        {
            $this->_apiSecret = $apiSecret;
        }
    }
