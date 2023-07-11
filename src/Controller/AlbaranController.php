<?php

namespace Drupal\commerce_albaran\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderTotalSummaryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Receipt Controller
 */
class AlbaranController extends ControllerBase {

  /** @var \Drupal\commerce_order\OrderTotalSummaryInterface */
  protected $orderTotalSummary;

  /**
   * Constructor
   */
  public function __construct(OrderTotalSummaryInterface $order_total_summary) {
    $this->orderTotalSummary = $order_total_summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_order.order_total_summary')
    );
  }

  /**
   * View receipt in the browser
   */
  public function viewAlbaran(OrderInterface $commerce_order) {
    $billing_profile = $commerce_order->getBillingProfile();

    $order = \Drupal::routeMatch()->getParameter('commerce_order');
    $order_id = $order->get('order_id')->value;
    $tempstore = \Drupal::service('tempstore.private')->get('commerce_albaran');
    $tempstore->set('id_order', $order_id);

    $order_items = $commerce_order->getItems();

    // Extrae el SKU de cada ítem y almacénalo en un array
    $skus = [];
    foreach ($order_items as $order_item) {
      $purchased_entity = $order_item->getPurchasedEntity();
      if ($purchased_entity) {
        $sku = $purchased_entity->getSku();
        $skus[] = $sku;
      }
    }

    return [
      '#theme' => 'commerce_order_albaran',
      '#order_entity' => $commerce_order,
      '#billing_information' => $billing_profile ? $this
        ->entityTypeManager()
        ->getViewBuilder('profile')
        ->view($billing_profile) : NULL,
      '#totals' => $this
        ->orderTotalSummary
        ->buildTotals($commerce_order),
      '#skus' => $skus
    ];
  }

}

