<?php

require ( "sphinxapi.php" );
require_once(dirname(__FILE__) . '/Config.php');

findArtistNodeId($_GET['q'], $_GET['pg']);

function findArtistNodeId($q, $pg) {
    $cl = new SphinxClient ();
    $cl->SetServer(SPHINX_HOST, SPHINX_PORT);

    $cl->SetConnectTimeout(1);
    $cl->SetArrayResult(true);
    $cl->SetWeights(array(100, 1));
    $cl->SetMatchMode(SPH_MATCH_PHRASE);

    if (!isset($pg)) {
        $pg = 0;
    }

    $cl->SetLimits($pg * 20, 20);


    //echo $q;
    $res = $cl->Query($q, 'artinfo_articles');
    //  var_dump($res);
    if ($res === false) {
        print "Query failed: " . $cl->GetLastError() . ".\n";
    }
    $total = 0;
    if (is_array($res["matches"])) {
        $total = $res["total"];
        print "Total Matches Found : " . $res["total"] . " " . "in " . $res[time] . " secs" . "<br><br>";
        foreach ($res["matches"] as $docinfo) {
            $title = getAttrValue("title_attr", $res, $docinfo);
            $url = ROOT_URL . getUrl($docinfo[id]);
            $htmltag = "<a href=\"$url\">$title </a>";
            print $htmltag;

            print "<br>";
        }
    }
    buildPagination($pg, $total, $q);
}

function buildPagination($pg, $total, $q) {
    echo "<BR><BR>";
    $TOTAL_PER_PAGE = 20;

    if (isset($pg) == FALSE) {
        $pg = 0;
    }
    $numOfLinks = $total / $TOTAL_PER_PAGE;

    for ($i = 0; $i < $numOfLinks; $i++) {
        $url = "/plan9/search.php?q=" . $q . "&pg=" . $i;
        echo "<a href=\"$url\">$i</a>";
        echo "  ";
    }
}

function getURL($docid) {

    $cl = new SphinxClient ();
    $cl->SetServer(SPHINX_HOST, SPHINX_PORT);

    $cl->SetConnectTimeout(1);
    $cl->SetArrayResult(true);
    $cl->SetWeights(array(100, 1));
    $cl->SetMatchMode(SPH_MATCH_ALL);

    $cl->SetFilter('nid_attr', array($docid));


    //echo $q;
    $res = $cl->Query('', 'url_alias');

    if ($res === false) {
        print "Query failed: " . $cl->GetLastError() . ".\n";
    }
    if (is_array($res["matches"])) {
        $n = 1;
        //print "Matches:\n";
        foreach ($res["matches"] as $docinfo) {

            $alias = getAttrValue('alias_attr', $res, $docinfo);
            //echo $alias;
            return $alias;
        }
    }
}

function getAttrValue($key, $res, $docinfo) {
    foreach ($res["attrs"] as $attrname => $attrtype) {
        $value = $docinfo["attrs"][$attrname];
        if (strcmp($key, $attrname) == 0) {
            return $value;
        }
    }
}

?>
