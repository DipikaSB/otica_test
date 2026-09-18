<?php
namespace Setblue\Shippingtracker\Plugin;

use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as ShipmentSave;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;

class ShipmentSavePlugin
{
    protected $request;
    protected $resource;

    public function __construct(
        RequestInterface $request,
        ResourceConnection $resource
    ) {
        $this->request = $request;
        $this->resource = $resource;
    }

    /**
     * After executing the shipment save controller, update link field in DB
     */
    public function afterExecute(ShipmentSave $subject, $result)
    {
        $postData = $this->request->getPostValue();
        if (!empty($postData['tracking'])) {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('sales_shipment_track');

            foreach ($postData['tracking'] as $track) {
                if (!empty($track['link']) && !empty($track['number'])) {
                    $connection->update(
                        $table,
                        ['link' => $track['link']],
                        ['track_number = ?' => $track['number']]
                    );
                }
            }
        }

        return $result;
    }
}
