<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal;
use Drupal\cluedo\Services\Repository;
use Drupal\cluedo\Services\SuggestionManager;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource that processes a suggestion and returns if/how disproved
 *
 * @RestResource(
 *   id = "suggest_resource",
 *   label = "Cluedo Suggestion Resource",
 *   uri_paths = {
 *   "create" = "/api/cluedo/guess"
 *   }
 * )
 */
class SuggestResource extends ResourceBase
{
  private Repository $repo;
  private SuggestionManager $suggestionManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, Repository $repo, SuggestionManager $suggestionManager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->repo = $repo;
    $this->suggestionManager = $suggestionManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ResourceBase|AccusationResource|static
  {
    /**
     * @var Repository $repo
     * @var SuggestionManager $suggestionManager
     */
    $repo = $container->get('cluedo.repository');
    $suggestionManager = $container->get('cluedo.suggestion_manager');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_rest'),
      $repo,
      $suggestionManager
    );
  }

  /**
   * handles POST request.
   * @throws Exception
   */
  public function post($data): ResourceResponse
  {
      $game = $this->repo->fetchGame(Drupal::request()->get('key'));

      if (!$game) {
        return new ResourceResponse("Spel niet gevonden");
      }

      if ($game->isGameOver()) {
        return new ResourceResponse("Deze zaak is reeds afgesloten");
      }

      $response = $this->suggestionManager->disproveSuggestion(
        $game->getWitnesses(),
        $data['karakter'],
        $data['wapen'],
        $data['kamer'],
      );

      return new ResourceResponse($response);
  }
}
