<?php

use Monolog\Logger;

class RoutesHelper
{

    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function dump($var)
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }

    public function detect_route($args, $app, $r, $response, $logged)
    {
        $exp = $args['exp'];
        $dataset = $args['dataset'];
        $detector = $args['detector'];
        if (!($exp === "ex1" || $exp === "ex2" || $exp === "ex3") || $detector == "" || $dataset == "") {
            return $response->withStatus(404);
        }
        $stats = $app->data->getIndex($exp, $dataset, $detector);
        if(!$stats || empty($stats)){
            return $response->withStatus(404);
        }
        return $r->renderer->render($response, 'detector.phtml',
            array('logged' => $logged, 'exp' => $exp, 'dataset' => $dataset, 'detector' => $detector,
                'projects' => $stats));
    }

    public function review_route($args, $app, $r, $response, $request, $logged, $review_flag)
    {
        $exp = $args['exp'];
        $set = $args['dataset'];
        $detector = $args['detector'];
        $project = $args['project'];
        $version = $args['version'];
        $misuse = $args['misuse'];
        $data = $app->data->getMetadata($misuse);
        $patterns = $app->data->getPatterns($misuse);
        $hits = $app->data->getHits($exp . "_" . $set . "_" . $detector, $project, $version, $misuse, $exp);
        if(empty($hits)){
            return $response->withStatus(404);
        }
        $reviewer = "";
        $review = NULL;
        if ($review_flag && !$logged) {
            $reviewer = $args['reviewer'];
        } else if ($review_flag && $logged) {
            $reviewer = $request->getServerParams()['PHP_AUTH_USER'];
        }
        $method = $hits ? ($exp == "ex2" ? $hits[0]['method'] : $data['method']) : "method not found";
        $code = $hits ? $hits[0]['target_snippets'] : "code not found";
        $line = $hits ? $hits[0]['line'] : 0;
        $file = $hits ? ($exp == "ex2" ? $hits[0]['file'] : $data['file']) : "file not found";
        $review = $app->data->getReview($exp, $set, $detector, $project, $version, $misuse, $reviewer);
        return $r->renderer->render($response, 'review.phtml',
            array('name' => $reviewer, 'review' => $review, 'set' => $set, 'logged' => $logged, 'exp' => $exp,
                'detector' => $detector, 'version' => $version, 'project' => $project, 'misuse' => $misuse,
                'desc' => $data['description'], 'fix_desc' => $data['fix_description'], 'diff_url' => $data['diff_url'],
                'violation_types' => $data['violation_types'],
                'method' => $method,
                'file' => $file,
                'code' => $code, 'line' => $line, 'pattern_code' => $patterns['code'],
                'pattern_line' => $patterns['line'], 'pattern_name' => $patterns['name'], 'hits' => $hits));
    }

}