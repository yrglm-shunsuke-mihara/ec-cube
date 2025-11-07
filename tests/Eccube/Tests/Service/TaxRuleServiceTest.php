<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Tests\Service;

use Eccube\Entity\BaseInfo;
use Eccube\Entity\Master\RoundingType;
use Eccube\Entity\TaxRule;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Service\TaxRuleService;

class TaxRuleServiceTest extends AbstractServiceTestCase
{
    /**
     * @var TaxRuleService
     */
    private $taxRuleService;

    /**
     * @var  TaxRuleRepository
     */
    protected $TaxRule1;

    /**
     * @var  BaseInfo
     */
    protected $BaseInfo;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->BaseInfo = $this->entityManager->getRepository(BaseInfo::class)->get();
        $this->BaseInfo->setOptionProductTaxRule(0);
        $this->TaxRule1 = $this->entityManager->getRepository(TaxRule::class)->find(1);
        $this->TaxRule1->setApplyDate(new \DateTime('-1 day'));
        static::getContainer()->get('doctrine')->getManager()->flush();
        $this->taxRuleService = static::getContainer()->get(TaxRuleService::class);
    }

    /**
     * @group decimal
     */
    public function testRoundByCalcRuleWithDefault()
    {
        $input = '100.4';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, 999);
        $this->verify();

        $input = '100.5';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, 999);
        $this->verify();

        $input = '100';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, 999);
        $this->verify();

        $input = '101';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, 999);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testRoundByRoundingTypeWithCeil()
    {
        $input = '100.4';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::CEIL);
        $this->verify();

        $input = '100.5';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::CEIL);
        $this->verify();

        $input = '100';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::CEIL);
        $this->verify();

        $input = '101';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::CEIL);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testRoundByRoundingTypeWithRound()
    {
        $input = '100.4';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::ROUND);
        $this->verify();

        $input = '100.5';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::ROUND);
        $this->verify();

        $input = '100';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::ROUND);
        $this->verify();

        $input = '101';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::ROUND);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testRoundByRoundingTypeWithFloor()
    {
        $input = '100.4';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::FLOOR);
        $this->verify();

        $input = '100.5';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::FLOOR);
        $this->verify();

        $input = '100';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::FLOOR);
        $this->verify();

        $input = '101';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::FLOOR);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTax()
    {
        $input = '1000';
        $rate = '8';
        $this->expected = '80.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::ROUND);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxWithAdjust()
    {
        $input = '1008';
        $rate = '8';
        $adjust = '-1';
        $this->expected = '80.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::ROUND, $adjust);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testGetTax()
    {
        $input = '1000';
        $this->expected = '100.00';
        $this->actual = $this->taxRuleService->getTax($input);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcIncTax()
    {
        $input = '1000';
        $this->expected = '1100.00';
        $this->actual = $this->taxRuleService->getPriceIncTax($input);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxWithPrecision()
    {
        $input = '333';
        $rate = '10';
        $this->expected = '33.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::ROUND);
        $this->verify();

        $input = '999';
        $rate = '8';
        $this->expected = '80.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::ROUND);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxWithFloorRounding()
    {
        $input = '1234';
        $rate = '10';
        $this->expected = '123.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::FLOOR);
        $this->verify();

        $input = '999';
        $rate = '8';
        $this->expected = '79.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::FLOOR);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxWithCeilRounding()
    {
        $input = '1234';
        $rate = '10';
        $this->expected = '124.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::CEIL);
        $this->verify();

        $input = '999';
        $rate = '8';
        $this->expected = '80.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::CEIL);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxIncluded()
    {
        $input = '1080';
        $rate = '8';
        $this->expected = '80';
        $this->actual = $this->taxRuleService->calcTaxIncluded($input, $rate, RoundingType::ROUND);
        $this->verify();

        $input = '1100';
        $rate = '10';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->calcTaxIncluded($input, $rate, RoundingType::ROUND);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxIncludedWithAdjust()
    {
        $input = '1080';
        $rate = '8';
        $adjust = '5';
        $this->expected = '80';
        $this->actual = $this->taxRuleService->calcTaxIncluded($input, $rate, RoundingType::ROUND, $adjust);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxIncludedWithFloorRounding()
    {
        $input = '1080';
        $rate = '8';
        $this->expected = '80';
        $this->actual = $this->taxRuleService->calcTaxIncluded($input, $rate, RoundingType::FLOOR);
        $this->verify();

        $input = '1099';
        $rate = '10';
        $this->expected = '99';
        $this->actual = $this->taxRuleService->calcTaxIncluded($input, $rate, RoundingType::FLOOR);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxIncludedWithCeilRounding()
    {
        $input = '1080';
        $rate = '8';
        $this->expected = '80';
        $this->actual = $this->taxRuleService->calcTaxIncluded($input, $rate, RoundingType::CEIL);
        $this->verify();

        $input = '1099';
        $rate = '10';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->calcTaxIncluded($input, $rate, RoundingType::CEIL);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testGetPriceIncTaxWithPrecision()
    {
        $input = '333';
        $this->expected = '366.00';
        $this->actual = $this->taxRuleService->getPriceIncTax($input);
        $this->verify();

        $input = '999';
        $this->expected = '1099.00';
        $this->actual = $this->taxRuleService->getPriceIncTax($input);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testCalcTaxWithLargeNumbers()
    {
        $input = '999999';
        $rate = '10';
        $this->expected = '100000.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::ROUND);
        $this->verify();

        $input = '123456';
        $rate = '8';
        $this->expected = '9876.00';
        $this->actual = $this->taxRuleService->calcTax($input, $rate, RoundingType::ROUND);
        $this->verify();
    }

    /**
     * @group decimal
     */
    public function testRoundByRoundingTypeWithPreciseDecimals()
    {
        $input = '100.123456';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::FLOOR);
        $this->verify();

        $input = '100.999999';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::CEIL);
        $this->verify();

        $input = '100.499999';
        $this->expected = '100';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::ROUND);
        $this->verify();

        $input = '100.500001';
        $this->expected = '101';
        $this->actual = $this->taxRuleService->roundByRoundingType($input, RoundingType::ROUND);
        $this->verify();
    }
}
