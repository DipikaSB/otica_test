<?php
namespace Phonepe\PG\Model\Constants;

class EndpointConstants
{
    const PROD_CHECKOUT_JS_URL  = 'https://mercury.phonepe.com/web/bundle/checkout.js';
    const UAT_CHECKOUT_JS_URL   = 'https://mercury.phonepe.com/web/bundle/checkout.js';
    const PROD_WEBHOOK_URL      = 'https://api.phonepe.com/apis/omx-service/v1/webhooks/configure';
    const UAT_WEBHOOK_URL       = 'https://api-preprod.phonepe.com/apis/pg-sandbox/configs/v1/webhooks';
    const PROD_DASHBOARD_URL    = 'https://business.phonepe.com/transactions/details/';
    const UAT_DASHBOARD_URL     = 'https://business.phonepe.com/transactions/details/';
    const PROD_EVENTS_URL       = "https://api.phonepe.com/apis/pg-ingestion/client/v1/backend/events/batch";
    const UAT_EVENTS_URL        = "https://api-preprod.phonepe.com/apis/pg-ingestion//client/v1/backend/events/batch";
}
