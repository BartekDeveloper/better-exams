<?php


// Example:
Controller::Route(["GET"], ["/", "index", "home"], function($in, $out) {
    $file = "index.html";
    
    try {
        $response = new Response();
        $response->SetContent(CONTENT__HTML);
        $response->SetStatus(STATUS_HTTP__OK);
        $response->SetPage("index.html");
        
        $out = $response->All();

    } catch(Exception $e) {
        Controller::Redirect::ErrorState("404", $this);
    }

    return true;
}, [
    "modules" => [
        "required" => [],
        "optional" => ["auth"],
    ]
]);

Controller::Redirect(["ErrorState"], ["404"], function($in, $out) {
    $response = new Response();
    $response->SetContent(CONTENT__HTML);
    $response->SetStatus(STATUS_HTTP__NOTFOUND);
    $response->SetData("404");

    $out = $response->All();
}, []);