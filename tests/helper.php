<?php declare(strict_types=1);

/* Create Cartesian test cases */
function cartesian(array $input): array {
    $result = [[]];

    foreach ($input as $key => $values) {
        $append = [];

        foreach($result as $product) {
            foreach($values as $item) {
                $product[$key] = $item;
                $append[] = $product;
            }
        }

        $result = $append;
    }

    return $result;
}