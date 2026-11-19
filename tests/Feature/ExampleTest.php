<?php

it('has a healthy up endpoint', function () {
    $this->get('/up')->assertOk();
});
