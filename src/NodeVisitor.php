<?php

declare(strict_types=1);

namespace t0mmy742\RespectValidationTranslation;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use RuntimeException;

use function file_get_contents;

class NodeVisitor extends NodeVisitorAbstract
{
    /** @var array<string, int[]> */
    private array $data = [];

    /**
     * @param string $file
     * @return array<string, int[]>
     */
    public function extractMessages(string $file): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $fileContents = file_get_contents($file);
        if ($fileContents === false) {
            throw new RuntimeException('Unable to get contents from file: ' . $file);
        }
        $stmts = $parser->parse($fileContents);
        if ($stmts === null) {
            throw new RuntimeException('Unable to parse contents from file: ' . $file);
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($stmts);

        $data = $this->data;
        $this->data = [];
        return $data;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Property) {
            if (isset($node->props[0]) && $node->props[0]->name == 'defaultTemplates') {
                $defaultTemplatesValue = $node->props[0]->default;
                if ($defaultTemplatesValue instanceof Array_) {
                    foreach ($defaultTemplatesValue->items as $item) {
                        if ($item instanceof ArrayItem) {
                            if ($item->value instanceof Array_) {
                                foreach ($item->value->items as $subItem) {
                                    if ($subItem instanceof ArrayItem) {
                                        $value = $subItem->value;
                                        if ($value instanceof String_) {
                                            $this->data[$value->value][] = $value->getLine();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
