<?php

    /**
     * Iconfinder
     * 
     * @link    https://github.com/getstencil/PHP-Iconfinder
     * @author  Oliver Nassar <oliver@getstencil.com>
     */
    class Iconfinder
    {
        /**
         * _base
         * 
         * @var     string (default: 'https://api.iconfinder.com/v3')
         * @access  protected
         */
        protected $_base = 'https://api.iconfinder.com/v3';

        /**
         * _id
         * 
         * @var     null|string
         * @access  protected
         */
        protected $_id = null;

        /**
         * _maxPerPage
         * 
         * @var     int (default: 100)
         * @access  protected
         */
        protected $_maxPerPage = 100;

        /**
         * _paths
         * 
         * @var     array
         * @access  protected
         */
        protected $_paths = array(
            'search' => '/icons/search'
        );

        /**
         * _requestTimeout
         * 
         * @var     int (default: 10)
         * @access  protected
         */
        protected $_requestTimeout = 10;

        /**
         * _secret
         * 
         * @var     null|string
         * @access  protected
         */
        protected $_secret = null;

        /**
         * __construct
         * 
         * @access  public
         * @param   string $id
         * @param   string $secret
         * @return  void
         */
        public function __construct(string $id, string $secret)
        {
            $this->_id = $id;
            $this->_secret = $secret;
        }

        /**
         * _addUrlParams
         * 
         * @access  protected
         * @param   string $url
         * @param   array $params
         * @return  string
         */
        protected function _addUrlParams(string $url, array $params): string
        {
            $query = http_build_query($params);
            $piece = parse_url($url, PHP_URL_QUERY);
            if ($piece === null) {
                $url = ($url) . '?' . ($query);
                return $url;
            }
            $url = ($url) . '&' . ($query);
            return $url;
        }

        /**
         * _getAuthQueryData
         * 
         * @access  protected
         * @return  array
         */
        protected function _getAuthQueryData(): array
        {
            $data = array(
                'client_id' => $this->_id,
                'client_secret' => $this->_secret
            );
            return $data;
        }

        /**
         * _getBase
         * 
         * @access  protected
         * @return  string
         */
        protected function _getBase(): string
        {
            $base = $this->_base;
            return $base;
        }

        /**
         * _getNormalizedVectorData
         * 
         * @access  protected
         * @param   string $term
         * @param   array $decodedResponse
         * @return  null|array
         */
        protected function _getNormalizedVectorData(string $term, array $decodedResponse): ?array
        {
            if (isset($decodedResponse['icons']) === false) {
                return null;
            }
            $vectors = array();
            $records = (array) $decodedResponse['icons'];
            foreach ($records as $record) {
                if (isset($record['raster_sizes']) === false) {
                    continue;
                }
                if (isset($record['vector_sizes']) === false) {
                    continue;
                }
                $urls = $this->_getVectorRecordUrls($record);
                if ($urls === null) {
                    continue;
                }
                if (isset($record['tags']) === false) {
                    continue;
                }
                if (isset($record['icon_id']) === false) {
                    continue;
                }
                $styles = array();
                if (isset($record['styles']) === true) {
                    foreach ($record['styles'] as $style) {
                        array_push($styles, $style['identifier']);
                    }
                }
                $vector = array(
                    'id' => $record['icon_id'],
                    'tags' => $record['tags'],
                    'color' => (int) $record['is_icon_glyph'] === 0,
                    'original_term' => $term,
                    'styles' => $styles,
                    'urls' => $urls
                );
                array_push($vectors, $vector);
            }
            return $vectors;
        }

        /**
         * _getRandomString
         * 
         * @see     https://stackoverflow.com/questions/4356289/php-random-string-generator
         * @access  protected
         * @param   int $length (default: 32)
         * @return  string
         */
        protected function _getRandomString(int $length = 32): string
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        /**
         * _getRequestStreamContext
         * 
         * @access  protected
         * @return  resource
         */
        protected function _getRequestStreamContext()
        {
            $requestTimeout = $this->_requestTimeout;
            $options = array(
                'http' => array(
                    'method'  => 'GET',
                    'timeout' => $requestTimeout
                )
            );
            $streamContext = stream_context_create($options);
            return $streamContext;
        }

        /**
         * _getTermSearchPath
         * 
         * @access  protected
         * @return  string
         */
        protected function _getTermSearchPath(): string
        {
            $path = $this->_paths['search'];
            return $path;
        }

        /**
         * _getTermSearchQueryData
         * 
         * @access  protected
         * @param   string $term
         * @param   array $options
         * @return  array
         */
        protected function _getTermSearchQueryData(string $term, array $options): array
        {
            $data = array(
                'client_id' => $this->_id,
                'client_secret' => $this->_secret,
                'query' => $term,
                'count' => (int) $options['limit'],
                'offset' => (int) $options['offset'],
                'premium' => 0,
                'vector' => 1,
                'nocache' => $this->_getRandomString()
            );
            return $data;
        }

        /**
         * _getTermSearchUrl
         * 
         * @access  protected
         * @param   string $term
         * @param   array $options
         * @return  string
         */
        protected function _getTermSearchUrl(string $term, array $options): string
        {
            $base = $this->_getBase();
            $path = $this->_getTermSearchPath();
            $data = $this->_getTermSearchQueryData($term, $options);
            $url = ($base) . ($path);
            $url = $this->_addUrlParams($url, $data);
            return $url;
        }

        /**
         * _getVectorRecordUrls
         * 
         * @access  protected
         * @param   array $record
         * @return  null|array
         */
        protected function _getVectorRecordUrls(array $record): ?array
        {
            $bitmap = false;
            $vector = false;
            foreach ($record['raster_sizes'] as $size) {
                if ((int) $size['size_width'] === 128) {
                    $bitmap = $size['formats'][0]['preview_url'];
                    break;
                }
            }
            if ($bitmap === false) {
                return null;
            }
            foreach ($record['vector_sizes'] as $size) {
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
         * _requestUrl
         * 
         * @access  protected
         * @param   string $url
         * @return  null|string
         */
        protected function _requestUrl(string $url): ?string
        {
            $streamContext = $this->_getRequestStreamContext();
            $response = file_get_contents($url, false, $streamContext);
            if ($response === false) {
                return null;
            }
            return $response;
        }

        /**
         * getIconsByTerm
         * 
         * @access  public
         * @param   string $term
         * @param   array $options
         * @return  null|array
         */
        public function getIconsByTerm(string $term, array $options): ?array
        {
            $url = $this->_getTermSearchUrl($term, $options);
            $response = $this->_requestUrl($url);
            if ($response === null) {
                return null;
            }
            $decodedResponse = json_decode($response, true);
            if ($decodedResponse === null) {
                return null;
            }
            $vectors = $this->_getNormalizedVectorData($term, $decodedResponse);
            if ($vectors === null) {
                return null;
            }
            return $vectors;
        }

        /**
         * getPath
         * 
         * @access  public
         * @param   string $url
         * @return  null|string
         */
        public function getPath(string $url): ?string
        {
            $data = $this->_getAuthQueryData();
            $url = $this->_addUrlParams($url, $data);
            $response = $this->_requestUrl($url);
            if ($response === null) {
                return null;
            }
            return $response;
        }
    }
