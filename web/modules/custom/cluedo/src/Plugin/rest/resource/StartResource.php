<?php

namespace Drupal\cluedo\Plugin\rest\resource;

use Drupal\cluedo\Models\CluedoGame;
use Drupal\cluedo\Services\SuggestionManager;
use Drupal\Core\Entity\EntityStorageException;
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

  private SuggestionManager $manager;

  /**
   * Constructs a new ExampleGetRestResource object.
   *
   * @param array $configuration A configuration array containing information about the plugin instance.
   * @param string $plugin_id The plugin_id for the plugin instance.
   * @param mixed $plugin_definition The plugin implementation definition.
   * @param array $serializer_formats The available serialization formats.
   * @param LoggerInterface $logger A logger instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats,
                              LoggerInterface $logger, SuggestionManager $manager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ResourceBase|SuggestResource|ContainerFactoryPluginInterface|static
  {
    /**
     * @var SuggestionManager $manager
     */
    $manager = $container->get('cluedo.suggestion_manager');

    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('example_node_rest'),
      $manager,
    );
  }

  /**
   * Responds to GET request, return game-key
   * @return ResourceResponse
   * @throws EntityStorageException
   * @throws Exception
   */
  public function get(): ResourceResponse
  {
    $newGame = new CluedoGame($this->manager);
    $newGame->storeNewGame();

    return new ResourceResponse(['key'=> $newGame->getGameKey()]);
  }


}
