<?php

// 9.0779° N, 77.3452° E
// PHP’s round($value, 1) works by rounding to the nearest tenth (0.1):
// If the next digit (hundredth place) is 5 or higher, it rounds up.
// If it’s below 5, it rounds down.
// | Original | 2nd decimal | Result   | Why              |
// | -------- | ----------- | -------- | ---------------- |
// | 9.0779   | 7 → ≥ 5     | **9.1**  | Rounded **up**   |
// | 77.3452  | 4 → < 5     | **77.3** | Rounded **down** |


$lat = 9.0779 ;
$long = 77.3452;

echo round($lat,0.01);
echo "\n";
echo round($long,0.01);
echo "\n";
echo 77.3 < 77.3452;
?>