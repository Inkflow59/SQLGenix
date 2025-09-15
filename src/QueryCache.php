<?php

/**
 * QueryCache class for caching SQL queries and their results.
 * 
 * This class provides a simple in-memory cache for SQL queries to improve
 * performance by avoiding repeated query construction and execution.
 */
class QueryCache {
    /**
     * Cache storage for queries.
     * @var array
     */
    private $queryCache = [];
    
    /**
     * Cache storage for results.
     * @var array
     */
    private $resultCache = [];
    
    /**
     * Maximum number of cached items.
     * @var int
     */
    private $maxCacheSize;
    
    /**
     * Cache TTL in seconds.
     * @var int
     */
    private $ttl;
    
    /**
     * Cache hit statistics.
     * @var array
     */
    private $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0
    ];
    
    /**
     * Constructor
     * 
     * @param int $maxCacheSize Maximum number of items to cache (default: 100)
     * @param int $ttl Time to live in seconds (default: 3600 = 1 hour)
     */
    public function __construct($maxCacheSize = 100, $ttl = 3600) {
        $this->maxCacheSize = $maxCacheSize;
        $this->ttl = $ttl;
    }
    
    /**
     * Generate a cache key from query and parameters.
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return string Cache key
     */
    private function generateKey($query, $params = []) {
        return md5($query . serialize($params));
    }
    
    /**
     * Check if a cached item has expired.
     * 
     * @param array $item Cached item with timestamp
     * @return bool True if expired, false otherwise
     */
    private function isExpired($item) {
        return (time() - $item['timestamp']) > $this->ttl;
    }
    
    /**
     * Remove expired items from cache.
     */
    private function cleanupExpired() {
        foreach ($this->queryCache as $key => $item) {
            if ($this->isExpired($item)) {
                unset($this->queryCache[$key]);
            }
        }
        
        foreach ($this->resultCache as $key => $item) {
            if ($this->isExpired($item)) {
                unset($this->resultCache[$key]);
            }
        }
    }
    
    /**
     * Enforce cache size limit by removing oldest items.
     */
    private function enforceCacheLimit() {
        while (count($this->queryCache) > $this->maxCacheSize) {
            // Remove the oldest item (first in array)
            array_shift($this->queryCache);
        }
        
        while (count($this->resultCache) > $this->maxCacheSize) {
            array_shift($this->resultCache);
        }
    }
    
    /**
     * Cache a built query string.
     * 
     * @param string $builderKey Unique key for the query builder state
     * @param string $query Built SQL query
     */
    public function cacheQuery($builderKey, $query) {
        $this->cleanupExpired();
        
        $this->queryCache[$builderKey] = [
            'query' => $query,
            'timestamp' => time()
        ];
        
        $this->enforceCacheLimit();
        $this->stats['sets']++;
    }
    
    /**
     * Get a cached query string.
     * 
     * @param string $builderKey Unique key for the query builder state
     * @return string|null Cached query or null if not found/expired
     */
    public function getCachedQuery($builderKey) {
        if (!isset($this->queryCache[$builderKey])) {
            $this->stats['misses']++;
            return null;
        }
        
        $item = $this->queryCache[$builderKey];
        if ($this->isExpired($item)) {
            unset($this->queryCache[$builderKey]);
            $this->stats['misses']++;
            return null;
        }
        
        $this->stats['hits']++;
        return $item['query'];
    }
    
    /**
     * Cache query results.
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @param mixed $result Query result
     */
    public function cacheResult($query, $params, $result) {
        $key = $this->generateKey($query, $params);
        $this->cleanupExpired();
        
        $this->resultCache[$key] = [
            'result' => $result,
            'timestamp' => time()
        ];
        
        $this->enforceCacheLimit();
        $this->stats['sets']++;
    }
    
    /**
     * Get cached query results.
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return mixed|null Cached result or null if not found/expired
     */
    public function getCachedResult($query, $params) {
        $key = $this->generateKey($query, $params);
        
        if (!isset($this->resultCache[$key])) {
            $this->stats['misses']++;
            return null;
        }
        
        $item = $this->resultCache[$key];
        if ($this->isExpired($item)) {
            unset($this->resultCache[$key]);
            $this->stats['misses']++;
            return null;
        }
        
        $this->stats['hits']++;
        return $item['result'];
    }
    
    /**
     * Clear all cached items.
     */
    public function clear() {
        $this->queryCache = [];
        $this->resultCache = [];
        $this->stats = ['hits' => 0, 'misses' => 0, 'sets' => 0];
    }
    
    /**
     * Clear cached items for a specific table (useful after INSERT/UPDATE/DELETE).
     * 
     * @param string $table Table name
     */
    public function invalidateTable($table) {
        foreach ($this->queryCache as $key => $item) {
            if (stripos($item['query'], "FROM $table") !== false ||
                stripos($item['query'], "UPDATE $table") !== false ||
                stripos($item['query'], "INSERT INTO $table") !== false ||
                stripos($item['query'], "DELETE FROM $table") !== false) {
                unset($this->queryCache[$key]);
            }
        }
        
        foreach ($this->resultCache as $key => $item) {
            // For result cache, we need to check the original query
            // This is simplified - in production you might want to store more metadata
            unset($this->resultCache[$key]);
        }
    }
    
    /**
     * Get cache statistics.
     * 
     * @return array Cache statistics
     */
    public function getStats() {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;
        
        return array_merge($this->stats, [
            'total_requests' => $total,
            'hit_rate_percentage' => round($hitRate, 2),
            'query_cache_size' => count($this->queryCache),
            'result_cache_size' => count($this->resultCache)
        ]);
    }
    
    /**
     * Set cache TTL.
     * 
     * @param int $ttl Time to live in seconds
     */
    public function setTTL($ttl) {
        $this->ttl = $ttl;
    }
    
    /**
     * Get cache TTL.
     * 
     * @return int Time to live in seconds
     */
    public function getTTL() {
        return $this->ttl;
    }
    
    /**
     * Set maximum cache size.
     * 
     * @param int $size Maximum number of items to cache
     */
    public function setMaxCacheSize($size) {
        $this->maxCacheSize = $size;
        $this->enforceCacheLimit();
    }
    
    /**
     * Check if caching is enabled.
     * 
     * @return bool True if caching is enabled
     */
    public function isEnabled() {
        return $this->maxCacheSize > 0 && $this->ttl > 0;
    }
}