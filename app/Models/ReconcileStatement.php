<?php

namespace App\Models;

/**
 * Class Bank Statement
 * @package App\Models
 */
class ReconcileStatement extends Base
{
    const TABLE                 = 'shopify_excel_reconciliation_statement';
    const PRIMARY_KEY           = self::ID;
    const UUID                  = 'uuid';
    const OrganizationID        = 'organization_id';
    const FileName              = 'file_name';
    const RawData               = 'raw_data';
    const Status                = 'status';
    const Source                = 'source';
    const MetaData              = 'meta_data';
    const ImportedAt            = 'imported_at';
    const ImportedBy            = 'imported_by';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $table = self::TABLE;
    protected $hidden = [self::RawData];
    protected $fillable = [self::FileName, self::OrganizationID, self::UUID, self::MetaData, self::RawData, self::Status, self::Source, self::ImportedAt, self::ImportedBy];

}
