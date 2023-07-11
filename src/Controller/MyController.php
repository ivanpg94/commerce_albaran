<?php

namespace Drupal\commerce_albaran\Controller;

use Dompdf\Options;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Dompdf\Dompdf;

class MyController extends ControllerBase {

  protected $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container
    );
  }

  public function downloadPdf() {
    $options = new Options();
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('dpi', 115);
    $options->set('defaultPaperOrientation', 'portrait');
    $dompdf = new Dompdf($options);
    $dompdf->setPaper('A4');

    $extension_list = \Drupal::service('extension.list.module');
    $filepath = $extension_list->getPath('commerce_albaran') . '/templates/commerce-order-albaran-imprimir.html.twig';

    $tempstore = \Drupal::service('tempstore.private')->get('commerce_albaran');
    $order_id = $tempstore->get('id_order');
    $order_entity = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($order_id);
    $totals = $order_entity->get('order_total');

    $fecha = $order_entity->created->value;
    $email = $order_entity->mail->value;
    $fecha = date('m/d/Y H:i:s', $fecha);

    $rendered_template = $this->container->get('twig')->render($filepath, [
      'order_entity' => $order_entity,
      'totals' => $totals,
      'email' => $email,
      'fecha' => $fecha
    ]);

    $dompdf->loadHtml($rendered_template);
    $dompdf->render();

    $output = $dompdf->output();
    $response = new Response($output);
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', 'attachment;filename=albaran.pdf');
    $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');
    return $response;
  }


}
