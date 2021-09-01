<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal\cluedo\Models\CluedoGame;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Resource to start a new cluedo game and provide a game-key.
 *
 * @RestResource(
 *   id = "start_resource",
 *   label = "Start Resource Cluedo",
 *   uri_paths = {
 *   "canonical" = "/api/cluedo/new-game"
 *   }
 * )
 */
class StartResource extends ResourceBase
{
  /**
   * Responds to GET request, return game-key
   * @return ResourceResponse
   * @throws EntityStorageException
   */
  public function get(): ResourceResponse
  {
    $newGame = new CluedoGame();
    $newGame->storeNewGame();

    return new ResourceResponse(['key'=> $newGame->getGameKey()]);
  }

}
