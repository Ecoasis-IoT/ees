<?php
/**
 * Shared DB-name-to-key mapping for EES per-site databases.
 * Include this file anywhere a site db_name needs to be resolved to a getDB() key.
 *
 * Usage: $key = ees_db_key($site['db_name']);
 */

if (!function_exists('ees_db_key')) {

    function ees_db_key(string $db_name): string {
        static $map = null;
        if ($map === null) {
            $map = [
                // Full database names
                'u889201362_factory'      => 'factory',
                'u889201362_gob'          => 'gob',
                'u889201362_the_pod'      => 'pod',
                'u889201362_r_terre_mall' => 'rtm',
                'u889201362_bovalon_mall' => 'bovalon',
                'u889201362_phoenix_mall' => 'phoenix',
                'u889201362_p_catering'   => 'p_catering',
                'u889201362_moka_city'    => 'moka_city',
                'u889201362_helvetia'     => 'moka_city',
                'u889201362_home_leisure' => 'home_leisure',
                'u889201362_case_noyal'   => 'case_noyal',
                // Config file basenames (as stored in tbl_site.db_name)
                'factory'          => 'factory',
                'gob_config'       => 'gob',
                'gob'              => 'gob',
                'pod_config'       => 'pod',
                'the_pod'          => 'pod',
                'pod'              => 'pod',
                'r_terre_mall'     => 'rtm',
                'rtm'              => 'rtm',
                'bovalon_mall'     => 'bovalon',
                'bovalon'          => 'bovalon',
                'phoenix_mall'     => 'phoenix',
                'phoenix'          => 'phoenix',
                'p_catering'       => 'p_catering',
                'moka_city'        => 'moka_city',
                'moka_city.php'    => 'moka_city',
                'helvetia'         => 'moka_city',
                'helvetia.php'     => 'moka_city',
                'home_leisure'     => 'home_leisure',
                'home_and_leisure' => 'home_leisure',
                'case_noyal'       => 'case_noyal',
                'case_noyal.php'   => 'case_noyal',
            ];
        }

        // Strip .php extension if present (tbl_site often stores e.g. phoenix_mall.php)
        $key = pathinfo($db_name, PATHINFO_FILENAME);
        // Prefer mapped short key; else basename so tryGetDB is not called with a .php string
        return $map[$db_name] ?? $map[$key] ?? $key;
    }

}

// Backward compat alias used in older files
if (!function_exists('_dbNameToKey')) {
    function _dbNameToKey(string $db_name): string {
        return ees_db_key($db_name);
    }
}
