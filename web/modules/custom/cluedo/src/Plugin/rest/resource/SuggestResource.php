<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal;
use Drupal\cluedo\Services\Repository;
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
    $players = $repo->fetchPlayersByKey(Drupal::request()->get('key'));

    foreach ($players as $player)
    {
      foreach ($player->getClues() as $clue)
      {
        if (in_array($clue->getName(), [$data['room'], $data['weapon'], $data['murderer']], true))
        {
          return new ResourceResponse
          ([
            'player' => $player->getName(),
            'disproves' => $clue->getName(),
            'type' => $clue->getType()
          ]);
        }
      }
    }
    return new ResourceResponse([
      'player' => '',
      'disproves' => '',
      'type' => ''
    ]);
  }
}
