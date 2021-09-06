<?php

namespace Drupal\cluedo\Models;

class Clue
{
  private int $nodeId;
  private string $type;
  private string $name;

  /**
   * @param int $nodeId
   * @param string $type
   * @param string $name
   */
  public function __construct(int $nodeId, string $type, string $name)
  {
    $this->nodeId = $nodeId;
    $this->type = $type;
    $this->name = $name;
  }


  public function getNodeId(): int
  {
    return $this->nodeId;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getName(): string
  {
    return $this->name;
  }


}
