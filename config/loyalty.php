<?php

return [

    // Points earned per 1 unit of currency spent.
    'points_per_dollar' => 1,

    // A customer is "close to a reward" when their balance is at or above
    // this fraction of the next reward threshold. 0.80 = 80%.
    'proximity_threshold' => 0.80,

    // A customer is "inactive" when their last activity is older than
    // this many days.
    'inactivity_days' => 14,

];
