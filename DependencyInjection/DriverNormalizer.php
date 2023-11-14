<?php

namespace Propel\Bundle\PropelBundle\DependencyInjection;

/**
 * Provides normalization for Database Driver values.
 *
 * Class DriverNormalizer
 * @package Propel\Bundle\PropelBundle\DependencyInjection
 */
class DriverNormalizer
{
    /**
     * Normalizes the database driver related keys in a Propel datasource configuration, specifically the 'adapter'
     * value & the dsn value of the connection configuration.
     *
     * @param array $datasource
     *
     * @return array
     */
    public function normalizeDatasource($datasource)
    {
        if ($adapter = $datasource['adapter'] ?? null)
        {
            $datasource['adapter'] = $this->normalize($adapter);
        }

        if ($dsn = $datasource['connection']['dsn'] ?? null)
        {
            $datasource['connection']['dsn'] = $this->normalize($dsn);
        }

        return $datasource;
    }

    /**
     * Removes the 'pdo_' prefix found in some database driver strings.
     *
     * @param string $value
     *
     * @return string
     */
    public function normalize($value)
    {
        return str_replace('pdo_', '', $value);
    }
}