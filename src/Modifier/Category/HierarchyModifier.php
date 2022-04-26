<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Category\Category;

class HierarchyModifier extends Modifier
{
    protected string $selectQuery = "
      SELECT
        oc.OXID
      FROM
        oxcategories oc
      WHERE
        oc.OXLEFT <= :left 
        AND oc.OXRIGHT >= :right 
        AND oc.OXROOTID = :rootId
      ORDER BY oc.OXLEFT;
    ";

    private Connection $database;

    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    /**
     * Modify product and return modified product
     *
     * @param Category $category
     *
     * @return Category
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $category)
    {
        $hierarchy = $this->database
            ->executeQuery(
                $this->selectQuery,
                [
                    'left'   => $category->OXLEFT,
                    'right'  => $category->OXRIGHT,
                    'rootId' => $category->OXROOTID,
                ]
            )
            ->fetchFirstColumn();

        $category->depth     = count($hierarchy);
        $category->hierarchy = implode('//', $hierarchy);

        return $category;
    }
}
