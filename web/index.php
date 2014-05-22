<?php
require __DIR__ . '/../vendor/autoload.php';

$parser = new Icecave\Dialekt\Parser\ExpressionParser(null, isset($_GET['orByDefault']));
$listParser = new Icecave\Dialekt\Parser\ListParser;
$renderer = new Icecave\Dialekt\Renderer\ExpressionRenderer;
$treeRenderer = new Icecave\Dialekt\Demo\HtmlTreeRenderer;
$evaluator = new Icecave\Dialekt\Evaluator\Evaluator;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Dialekt Parser Demo</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <script>
            window.onload = function () {
                document.getElementById('expr').focus();
            }
        </script>
    </head>
    <body>
        <div class="container">
            <section>
                <h1>Dialekt Expression Parser</h1>
                <form method="get">
                    <p>
                    This page demonstrates how tag expressions are parsed to generate an abstract syntax tree (AST) and how
                    the built-in evaluator applies to sets of tags. The AST can be traversed to produce the desired output,
                    for example an SQL "WHERE" clause that finds entries with the matching tags.
                    </p>
                    <p>
                    In the <strong>Dialekt Expression</strong> field, enter a list of tags separated by spaces.
                    Optionally use the <strong>AND</strong>, <strong>OR</strong> and <strong>NOT</strong> keywords to
                    perform boolean operations. Expressions grouped in brackets are evaluated first.
                    </p>
                    <p>
                    By default, two adjacent tags are treated as an <strong>AND</strong> operation. This behavior can be
                    changed by selecting the checkbox below.
                    </p>
                    <input id="expr" type="text" value="<?=htmlentities($_GET['expr'])?>" name="expr" placeholder="Dialekt expression ...">
                    <label><input name="orByDefault" type="checkbox" <?=isset($_GET['orByDefault']) ? ' checked' : ''?>> Use <strong>OR</strong> operator by default.</label>

                    <p>
                    You can optionally provide a space-separated list of tags to evaluate against the expression.
                    </p>
                    <input id="tags" type="text" value="<?=htmlentities($_GET['tags'])?>" name="tags" placeholder="Tag list ...">

                    <input id="submit" type="submit" value="Parse &amp; Evaluate">
                </form>
            </section>

            <?php
            try {

                $tags = [];

                if (isset($_GET['tags'])) {
                    $tags = $listParser->parseAsArray($_GET['tags']);
                }

            } catch (Icecave\Dialekt\Parser\Exception\ParseException $e) {

                echo '<section class="error">';
                echo '<h1>Parse Error in Tag List</h1>';
                echo '<p>' . htmlentities($e->getMessage()) . '</p>';
                echo '</section>';
            }

            if (isset($_GET['expr'])) {
                try {
                    $expression = $parser->parse($_GET['expr']);

                    if ($tags) {
                        $evaluationResult = $evaluator->evaluate($expression, $tags);
                    } else {
                        $evaluationResult = null;
                    }

                    echo '<section>';
                    echo '<h1>Normalized Expression</h1>';
                    echo '<pre>' . htmlentities($renderer->render($expression)) . '</pre>';
                    echo '</section>';

                    echo '<section>';
                    echo '<h1>Syntax Tree</h1>';
                    echo '<div>' . $treeRenderer->render($expression, $evaluationResult) . '</div>';
                    echo '</section>';

                } catch (Icecave\Dialekt\Parser\Exception\ParseException $e) {

                    echo '<section class="error">';
                    echo '<h1>Parse Error in Expression</h1>';
                    echo '<p>' . htmlentities($e->getMessage()) . '</p>';
                    echo '</section>';

                }
            }
            ?>
        </div>
        <footer>
            Powered by <a href="https://github.com/IcecaveStudios/dialekt">Dialekt</a> v<?=htmlentities(Icecave\Dialekt\PackageInfo::VERSION)?>
        </footer>
    </body>
</html>
