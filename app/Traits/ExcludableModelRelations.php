<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait ExcludableModelRelations
{
    protected function applyExcludes($query, Request $request)
    {
        // Handle relationship exclusions
        $excludeRelations = $this->parseExcludes($request->get('exclude', ''));
        if (!empty($excludeRelations)) {
            $query->without($excludeRelations);
        }

        // Handle attribute exclusions (from fillable/guarded)
        $excludeAttributes = $this->parseExcludes($request->get('exclude_attributes', ''));
        if (!empty($excludeAttributes)) {
            $this->applyAttributeExclusions($query, $excludeAttributes);
        }

        // Handle column exclusions (from database schema)
        $excludeColumns = $this->parseExcludes($request->get('exclude_columns', ''));
        if (!empty($excludeColumns)) {
            $this->applyColumnExclusions($query, $excludeColumns);
        }

        return $query;
    }

    private function applyAttributeExclusions($query, $excludeAttributes)
    {
        $model = $query->getModel();
        $table = $model->getTable();

        // Get model attributes (fillable)
        $fillable = $model->getFillable();
        if (empty($fillable)) {
            return; // No fillable attributes defined
        }

        // Remove excluded attributes
        $includedAttributes = array_diff($fillable, $excludeAttributes);

        // Always include primary key
        $primaryKey = $model->getKeyName();
        if (!in_array($primaryKey, $includedAttributes)) {
            array_unshift($includedAttributes, $primaryKey);
        }

        // Add timestamps if model uses them
        if ($model->timestamps) {
            $createdAt = $model->getCreatedAtColumn();
            $updatedAt = $model->getUpdatedAtColumn();

            if (!in_array($createdAt, $includedAttributes)) {
                $includedAttributes[] = $createdAt;
            }
            if (!in_array($updatedAt, $includedAttributes)) {
                $includedAttributes[] = $updatedAt;
            }
        }

        // Add table prefix
        $selectColumns = array_map(function ($attr) use ($table) {
            return $table . '.' . $attr;
        }, $includedAttributes);

        $query->select($selectColumns);
    }

    private function applyColumnExclusions($query, $excludeColumns)
    {
        $model = $query->getModel();
        $table = $model->getTable();

        // Get all columns from database
        $allColumns = Schema::getColumnListing($table);

        // Remove excluded columns
        $includedColumns = array_diff($allColumns, $excludeColumns);

        // Ensure primary key is included
        $primaryKey = $model->getKeyName();
        if (!in_array($primaryKey, $includedColumns)) {
            array_unshift($includedColumns, $primaryKey);
        }

        // Add table prefix
        $selectColumns = array_map(function ($column) use ($table) {
            return $table . '.' . $column;
        }, $includedColumns);

        $query->select($selectColumns);
    }

    private function parseExcludes($excludeString)
    {
        if (empty($excludeString)) {
            return [];
        }

        return array_map('trim', explode(',', $excludeString));
    }
}
