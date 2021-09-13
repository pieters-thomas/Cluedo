<?php

namespace Drupal\cluedo\Models\Clues;

class CluedoClue
{
private int $nodeId;
private string $name;

  public function __construct(int $nodeId, string $name)
  {
    $this->nodeId = $nodeId;
    $this->name = $name;
  }

  public function getNodeId(): int
  {
    return $this->nodeId;
  }

  public function getName(): string
  {
    return $this->name;
  }


}
