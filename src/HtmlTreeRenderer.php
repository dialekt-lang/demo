<?php
namespace Icecave\Dialekt\Demo;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\LogicalNot;
use Icecave\Dialekt\AST\LogicalOr;
use Icecave\Dialekt\AST\NodeInterface;
use Icecave\Dialekt\AST\Pattern;
use Icecave\Dialekt\AST\PatternLiteral;
use Icecave\Dialekt\AST\PatternWildcard;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\AST\VisitorInterface;
use Icecave\Dialekt\Evaluator\EvaluationResult;
use Icecave\Dialekt\Renderer\RendererInterface;

/**
 * Render an AST expression to a string representing the tree structure.
 */
class HtmlTreeRenderer implements RendererInterface, VisitorInterface
{
    /**
     * Render an expression to a string.
     *
     * @param ExpressionInterface $expression The expression to render.
     *
     * @return string The rendered expression.
     */
    public function render(
        ExpressionInterface $expression,
        EvaluationResult $evaluationResult = null
    ) {
        $this->result = $evaluationResult;

        $html  = '<ul class="syntax-tree">';
        $html .= $this->renderList([$expression]);
        $html .= '</ul>';

        return $html;
    }

    /**
     * Visit a LogicalAnd node.
     *
     * @internal
     *
     * @param LogicalAnd $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalAnd(LogicalAnd $node)
    {
        $html .= '<span class="node-label">AND</span>';
        $html .= $this->renderResult($node);
        $html .= $this->renderList($node->children());

        return $html;
    }

    /**
     * Visit a LogicalOr node.
     *
     * @internal
     *
     * @param LogicalOr $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalOr(LogicalOr $node)
    {
        $html .= '<span class="node-label">OR</span>';
        $html .= $this->renderResult($node);
        $html .= $this->renderList($node->children());

        return $html;
    }

    /**
     * Visit a LogicalNot node.
     *
     * @internal
     *
     * @param LogicalNot $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalNot(LogicalNot $node)
    {
        $html .= '<span class="node-label">NOT</span>';
        $html .= $this->renderResult($node);
        $html .= $this->renderList([$node->child()]);

        return $html;
    }

    /**
     * Visit a Tag node.
     *
     * @internal
     *
     * @param Tag $node The node to visit.
     *
     * @return mixed
     */
    public function visitTag(Tag $node)
    {
        $html .= '<span class="node-label">TAG</span>';
        $html .= '<span class="node-data">';
        $html .= json_encode($node->name());
        $html .= '</span>';
        $html .= $this->renderResult($node);

        return $html;
    }

    /**
     * Visit a Pattern node.
     *
     * @internal
     *
     * @param Pattern $node The node to visit.
     *
     * @return mixed
     */
    public function visitPattern(Pattern $node)
    {
        $html .= '<span class="node-label">PATTERN</span>';
        $html .= $this->renderResult($node);
        $html .= $this->renderList($node->children());

        return $html;
    }

    /**
     * Visit a PatternLiteral node.
     *
     * @internal
     *
     * @param PatternLiteral $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternLiteral(PatternLiteral $node)
    {
        $html .= '<span class="node-label">LITERAL</span>';
        $html .= '<span class="node-data">';
        $html .= json_encode($node->string());
        $html .= '</span>';

        return $html;
    }

    /**
     * Visit a PatternWildcard node.
     *
     * @internal
     *
     * @param PatternWildcard $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternWildcard(PatternWildcard $node)
    {
        $html .= '<span class="node-label">WILDCARD</span>';

        return $html;
    }

    /**
     * Visit a EmptyExpression node.
     *
     * @internal
     *
     * @param EmptyExpression $node The node to visit.
     *
     * @return mixed
     */
    public function visitEmptyExpression(EmptyExpression $node)
    {
        $html .= '<span class="node-label">EMPTY</span>';
        $html .= $this->renderResult($node);

        return $html;
    }

    private function renderList($nodes)
    {
        $html = '<ul>';

        foreach ($nodes as $n) {
            $html .= '<li class="node">';

            if ($n instanceof ExpressionInterface && $this->result) {
                $result = $this->result->expressionResult($n);
                if ($result->isMatch()) {
                    $html .= '<span class="node-status success">&#x2714</span> ';
                } else {
                    $html .= '<span class="node-status error">&#x2718;</span> ';
                }
            } else {
                    $html .= '<span class="node-status">&mdash;</span> ';
            }

            $html .= $n->accept($this);
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    private function renderResult(ExpressionInterface $expression)
    {
        if (!$this->result) {
            return;
        }

        $result = $this->result->expressionResult($expression);

        $html  = '<span class="node-result">';

        if ($result->matchedTags()) {
            $html .= '<span class="success">';
            $html .= '(';
            $html .= implode(
                ', ',
                array_map(
                    'htmlentities',
                    $result->matchedTags()
                )
            );
            $html .= ') ';
            $html .= '</span>';
        }

        if ($result->unmatchedTags()) {
            $html .= '<span class="error">';
            $html .= '(';
            $html .= implode(
                ', ',
                array_map(
                    'htmlentities',
                    $result->unmatchedTags()
                )
            );
            $html .= ')';
            $html .= '</span>';
        }

        $html .= '</span>';

        return $html;
    }

    private $expressionResult;
}
