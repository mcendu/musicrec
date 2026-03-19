<?php

namespace Tests\Unit\Models;

use App\Models\TrackUrl;

it('uses the urls table', function () {
    $model = new TrackUrl();

    expect($model->getTable())->toEqual('urls');
});
