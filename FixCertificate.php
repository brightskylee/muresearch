<?php

$context = array(
    "ssl"=>array(
        "cafile" => "/etc/pki/tls/cert.pem"
    ),
);

$context = stream_context_create($context);

?>
