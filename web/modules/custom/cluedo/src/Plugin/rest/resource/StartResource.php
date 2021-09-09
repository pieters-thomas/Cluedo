<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal;
use Drupal\cluedo\Services\GameManager;
use Drupal\cluedo\Services\Repository;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  private GameManager $gameManager;
  private Repository $repo;

  /**
   * Constructs a new ExampleGetRestResource object.
   *
   * @param array $configuration A configuration array containing information about the plugin instance.
   * @param string $plugin_id The plugin_id for the plugin instance.
   * @param mixed $plugin_definition The plugin implementation definition.
   * @param array $serializer_formats The available serialization formats.
   * @param LoggerInterface $logger A logger instance.
   */
  public function __construct(array           $configuration, $plugin_id, $plugin_definition, array $serializer_formats,
                              LoggerInterface $logger, GameManager $manager, Repository $repo)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->gameManager = $manager;
    $this->repo = $repo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ResourceBase|SuggestResource|ContainerFactoryPluginInterface|static
  {
    /**
     * @var GameManager $manager
     * @var Repository $repo
     */
    $manager = $container->get('cluedo.game_manager');
    $repo = $container->get('cluedo.repository');
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('example_node_rest'),
      $manager,
      $repo
    );
  }

  /**
   * Responds to GET request
   * Expected get parameters (naam, aantal)
   * @return ResourceResponse
   * @throws Exception
   */
  public function get(): ResourceResponse
  {
    $playerAmount = (int)htmlspecialchars(Drupal::request()->get('aantal'), ENT_QUOTES);
    $gameKey = $this->gameManager->createNewGame($this->repo, $playerAmount);

    return new ResourceResponse(['key' => $gameKey]);

  }

}
