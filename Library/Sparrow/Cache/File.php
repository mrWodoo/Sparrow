<?php
namespace Sparrow\Cache;


use Sparrow\DependencyInjection;
use Sparrow\Exception;

class File {
    const CACHE_OK = 1;
    const CACHE_NOT_FOUND = 2;
    const CACHE_TIMEOUT = 3;

    /**
     * Storage dir
     *
     * @var string
     */
    protected $_storage = '';

    /**
     * Cache
     *
     * @var string
     */
    protected $_cache = '';

    /**
     * Last loaded cache state
     *
     * @var integer
     */
    protected $_lastState = 0;

    /**
     * DI
     *
     * @var DependencyInjection
     */
    protected $_di = null;

    /**
     * Check for chmods
     *
     * @param DependencyInjection $di
     * @throws Exception
     */
    public function __construct( DependencyInjection $di ) {
        $this->_di = $di;

        // Check for CHMODS
        $storage = $di->ConfigReader->cache->storage->get();
        $cache = $di->ConfigReader->cache->cache->get();

        if( ( !file_exists( $storage ) || !file_exists( $cache ) ||
            ( !is_writeable( $storage ) || !is_writeable( $cache ) ) ||
            ( !is_readable( $storage) || !is_readable( $cache ) ) ) ) {
                throw new Exception( 'There is a problem with storage/cache directories! (CHMODS maybe?)' );
        }

        $this->_storage = $storage;
        $this->_cache = $cache;
    }

    /**
     * Read cache, if you want to check if timeout then look at Cache\File::getState()
     *
     * @param string $cacheId
     * @return multitype:|boolean
     */
    public function read( $cacheId ) {
        if( file_exists( $this->_cache . $cacheId . '.php' ) ) {

            $cacheData = array();
            require_once( $this->_cache . $cacheId . '.php' );

            if( $this->_di->Application->getTime() > $cacheData['timeout'] ) {
                $this->_lastState = self::CACHE_TIMEOUT;
            } else {
                $this->_lastState = self::CACHE_OK;
            }

            return $cacheData['data'];
        } else {
           $this->_lastState = self::CACHE_NOT_FOUND;
            return false;
        }
    }

    /**
     * Create cache
     *
     * @param sring $cacheId
     * @param mixed $data
     * @param integer $timeout
     */
    public function create( $cacheId, $data, $timeout = 0 ) {
        // Permanent cache?
        if( $timeout <= 0 ) {
            $timeout = 60 * 60 * 24 * 365;
        }

        $cacheData = array( 'timeout' => $this->_di->Application->getTime() + $timeout, 'data' => $data );

        $fileContent = '<?php' . "\n";
        $fileContent .= '$cacheData = ' . var_export( $cacheData, true ) . ';' . "\n";
        $fileContent .= '?>';

        file_put_contents( $this->_cache .  $cacheId . '.php', $fileContent );
    }

    /**
     * Delete cache
     *
     * @param string $cacheId
     */
    public function delete( $cacheId ) {
        if( file_exists( $this->_cache . $cacheId . '.php' ) ) {
            unlink( $this->_cache . $cacheId . '.php' );
        }
    }

    /**
     * Get state of last loaded cache
     *
     * @return integer
     */
    public function getState() {
        return $this->_lastState;
    }
}