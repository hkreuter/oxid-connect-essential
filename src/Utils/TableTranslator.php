<?php

namespace Makaira\OxidConnectEssential\Utils;

use Closure;

class TableTranslator
{
    /**
     * @var array<string>
     */
    private array $searchTables;

    /**
     * @var string
     */
    private string $language = 'de';

    /**
     * @var int | null
     */
    private ?int $shopId = null;

    /**
     * @var Closure
     */
    private Closure $viewNameGenerator;

    /**
     * TableTranslator constructor.
     *
     * @param string[] $searchTables
     */
    public function __construct(array $searchTables)
    {
        $this->searchTables = $searchTables;

        $this->viewNameGenerator = static function ($table, $language, $shopId = null) {
            if (null !== $shopId) {
                $table = "{$table}_{$shopId}";
            }

            return "oxv_{$table}_{$language}";
        };
    }

    /**
     * @param Closure $viewNameGenerator
     *
     * @return TableTranslator
     */
    public function setViewNameGenerator(Closure $viewNameGenerator): TableTranslator
    {
        $this->viewNameGenerator = $viewNameGenerator;

        return $this;
    }

    /**
     * Set the language
     *
     * @param string $language
     *
     * @return TableTranslator
     */
    public function setLanguage(string $language): TableTranslator
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param int|string|null $shopId
     *
     * @return TableTranslator
     */
    public function setShopId($shopId): TableTranslator
    {
        $this->shopId = (int) $shopId;

        return $this;
    }

    /**
     * Translate an sql query
     *
     * @param string $sql
     *
     * @return string
     */
    public function translate(string $sql): string
    {
        foreach ($this->searchTables as $searchTable) {
            $viewNameGenerator = $this->viewNameGenerator;
            $replaceTable = $viewNameGenerator($searchTable, $this->language, $this->shopId);
            $sql          = (string) preg_replace_callback(
                "((?P<tableName>{$searchTable})(?P<end>[^A-Za-z0-9_]|$))",
                static fn ($match) => $replaceTable . $match['end'],
                $sql
            );
        }

        return $sql;
    }
}