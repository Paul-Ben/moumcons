<?php

namespace App\Enums;

/** PRD §18 — document library categories. */
enum DownloadCategory: string
{
    use TraitHasStatusLabels;

    case CompanyProfile = 'company_profile';
    case Brochures = 'brochures';
    case ServiceCatalogues = 'service_catalogues';
    case Forms = 'forms';
    case Reports = 'reports';
    case Policies = 'policies';
    case TrainingDocuments = 'training_documents';
    case TenderDocuments = 'tender_documents';
}
