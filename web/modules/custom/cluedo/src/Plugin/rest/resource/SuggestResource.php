<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal;
use Drupal\cluedo\Services\Repository;
use Drupal\cluedo\Services\SuggestionManager;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Exception;

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

  /**
   * handles POST request.
   * @throws Exception
   */
  public function post($data): ResourceResponse
  {

    $repo = new Repository();
    $suggestionManager = new SuggestionManager();

    $players = $repo->fetchPlayersByKey(Drupal::request()->get('key'));
    $response = $suggestionManager->disproveSuggestion(
      $players,
      $data['room'],
      $data['weapon'],
      $data['murderer']
    );

    return new ResourceResponse($response);
  }
}
