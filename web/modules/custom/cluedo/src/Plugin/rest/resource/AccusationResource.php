<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal;
use Drupal\cluedo\Services\Repository;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

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
  public function post($data): ResourceResponse
  {
    $repo = new Repository();
    $solution = $repo->fetchSolutionByKey(Drupal::request()->get('key'));
    if ($solution->equalsSuggested($data['room'], $data['weapon'], $data['murderer'])) {
      return new ResourceResponse([
        'outcome' => 'You win!',
        'message' => 'Well done super sleuth you\'ve cracked the case',
        ]);
    }
    return new ResourceResponse([
      'outcome' => 'Game over',
      'message' => 'Looks like you didn\'t quite figure it out.',
      ]);
  }
}
