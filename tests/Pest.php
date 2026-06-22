<?php

declare(strict_types=1);

/*
 * Unit tests for the pure compose/parse core need no Laravel bootstrap.
 * Feature tests (the Filament field) bind the Testbench TestCase explicitly.
 */
uses()->in('Unit');
