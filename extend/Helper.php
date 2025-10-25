<?php

/**
 * Helper utility class
 */
class Helper
{
    /**
     * Convert snake_case database fields to camelCase API fields
     * @param array $data
     * @return array
     */
    public static function toCamelCase($data)
    {
        if (empty($data)) {
            return [];
        }
        
        $result = [];
        foreach ($data as $key => $value) {
            // Convert to camelCase key
            $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            
            // Recurse when the value is an array
            if (is_array($value)) {
                $result[$camelKey] = self::toCamelCase($value);
            } else {
                $result[$camelKey] = $value;
            }
        }
        return $result;
    }
    
    /**
     * Convert camelCase API fields to snake_case database fields
     * @param array $data
     * @return array
     */
    public static function toSnakeCase($data)
    {
        if (empty($data)) {
            return [];
        }
        
        $result = [];
        foreach ($data as $key => $value) {
            // Convert to snake_case key
            $snakeKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
            
            // Recurse when the value is an array
            if (is_array($value)) {
                $result[$snakeKey] = self::toSnakeCase($value);
            } else {
                $result[$snakeKey] = $value;
            }
        }
        return $result;
    }
}



