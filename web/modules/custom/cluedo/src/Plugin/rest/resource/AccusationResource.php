<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal;
use Drupal\cluedo\Services\Repository;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource that processes a suggestion and returns if/how disproved
 *
 * @RestResource(
 *   id = "accusation_resource",
 *   label = "Cluedo Accusation Resource",
 *   uri_paths = {
 *   "create" = "/api/cluedo/accuse"
 *   }
 * )
 */
class AccusationResource extends ResourceBase
{
  private Repository $repo;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, Repository $repo)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->repo = $repo;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ResourceBase|AccusationResource|static
  {
    /**
     * @var Repository $repo
     */
    $repo = $container->get('cluedo.repository');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_rest'),
      $repo
    );
  }

  public function post($data): ResourceResponse
  {

      $solution = $this->repo->fetchSolutionByKey(Drupal::request()->get('key'));

      if ($solution->equalsSuggested($data['kamer'], $data['wapen'], $data['karakter']))
      {
        return new ResourceResponse([
          'message' => 'Correct, goed gedaan super speurneus!',
          'correct' => true,
        ]);
      }
      return new ResourceResponse([
        'message' => 'Helaas, dit was niet het juiste antwoord.',
        'correct' => false,
      ]);


  }
}
