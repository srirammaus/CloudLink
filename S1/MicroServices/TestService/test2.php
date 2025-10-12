<?php
function modifyByValue($num) {
    $num += 10;
    echo "Inside function (by value): $num\n";
}

function modifyByReference(&$num) {
    $num += 10;
    echo "Inside function (by reference): $num\n";
}

$originalNum = 5;

echo "Original number: $originalNum\n";

// Pass by value
modifyByValue($originalNum);
echo "After modifyByValue: $originalNum\n"; // Output: 5 (original not changed)

echo "\n";

// Pass by reference
modifyByReference($originalNum);
echo "After modifyByReference: $originalNum\n"; // Output: 15 (original changed)
?>