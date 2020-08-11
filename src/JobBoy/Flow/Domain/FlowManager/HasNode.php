<?php

namespace JobBoy\Flow\Domain\FlowManager;

interface HasNode
{
    public function node(): Node;
}