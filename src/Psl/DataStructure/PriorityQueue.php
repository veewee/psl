<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
use Psl\Arr;
use Psl\Math;

/**
 * @psalm-template T
 *
 * @implements PriorityQueueInterface<T>
 */
final class PriorityQueue implements PriorityQueueInterface
{
    /**
     * @psalm-var array<int, list<T>>
     */
    private array $queue = [];

    /**
     * Adds a node to the queue.
     *
     * @psalm-param T $node
     */
    public function enqueue(int $priority, $node): void
    {
        $nodes = $this->queue[$priority] ?? [];
        $nodes[] = $node;

        $this->queue[$priority] = $nodes;
        $this->queue = Arr\filter(
            $this->queue,
            static fn(array $list): bool => Arr\count($list) !== 0
        );
    }

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function peek()
    {
        if (0 === $this->count()) {
            return null;
        }

        /** @psalm-suppress MissingThrowsDocblock - we are sure that the queue is not empty. */
        return $this->fetch(false);
    }

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function pull()
    {
        if (0 === $this->count()) {
            return null;
        }

        /** @psalm-suppress MissingThrowsDocblock - we are sure that the queue is not empty. */
        return $this->fetch(true);
    }

    /**
     * Dequeues a node from the queue.
     *
     * @psalm-return T
     *
     * @throws Psl\Exception\InvariantViolationException If the Queue is invalid.
     */
    public function dequeue()
    {
        return $this->fetch(true);
    }

    /**
     * @psalm-return T
     *
     * @throws Psl\Exception\InvariantViolationException If the Queue is invalid.
     */
    private function fetch(bool $remove)
    {
        Psl\invariant(0 !== $this->count(), 'Cannot dequeue a node from an empty Queue.');

        // Retrieve the list of priorities.
        $priorities = Arr\keys($this->queue);
        /**
         * Retrieve the highest priority.
         *
         * @var int $priority
         */
        $priority = Math\max($priorities);
        // Retrieve the list of nodes with the priority `$priority`.
        $nodes = Arr\at($this->queue, $priority);
        /**
         * Retrieve the first node of the list.
         *
         * @psalm-suppress MissingThrowsDocblock - we are sure that the list is not empty.
         */
        $node = Arr\firstx($nodes);

        // Remove the node if we are supposed to.
        if ($remove) {
            // If the list contained only this node,
            // remove the list of nodes with priority `$priority`.
            if (1 === Arr\count($nodes)) {
                unset($this->queue[$priority]);
            } else {
                // otherwise, drop the first node.
                $this->queue[$priority] = Arr\values(Arr\drop($nodes, 1));
            }
        }

        return $node;
    }

    /**
     * Count the nodes in the queue.
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->queue as $priority => $list) {
            $count += Arr\count($list);
        }

        return $count;
    }
}
