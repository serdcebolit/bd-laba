<?php

namespace App\Controller;

use App\Controller;
use App\Tables\ProductTable;
use App\Tables\SectionTable;
use App\Tables\StockTable;
use App\ViewManager;

class ProductController extends Controller
{
	public static function show()
	{
		ViewManager::show('header', ['title' => 'Товары']);

		$result['currentUrl'] = $_SERVER['REQUEST_URI'];
		$result['result'] = [
			'columns' => [
				'ID', 'Название', 'Категория', 'Цена', 'Склады'
			]
		];

		$ob = ProductTable::query()
			->registerRuntimeField('SECTION', [
				'data_class' => SectionTable::class,
				'reference' => [
					'this' => 'SECTION_ID',
					'ref' => 'ID',
				],
				'join_type' => 'inner'
			])
			->registerRuntimeField('PRODUCT_STOCK', [
				'data_class' => ProductTable::PRODUCT_STOCK_TABLE,
				'reference' => [
					'this' => 'ID',
					'ref' => 'PRODUCT_ID',
				],
				'join_type' => 'inner'
			])
			->registerRuntimeField('STOCK', [
				'data_class' => StockTable::class,
				'reference' => [
					'this' => 'PRODUCT_STOCK.STOCK_ID',
					'ref' => 'ID',
				],
				'join_type' => 'inner'
			])
			->addOrder('ID')
			->addSelect('product.ID', 'PRODUCT_ID')
			->addSelect('product.NAME', 'PRODUCT_NAME')
			->addSelect('SECTION.NAME', 'SECTION_NAME')
			->addSelect('product.PRICE', 'PRODUCT_PRICE')
			->addSelect('STOCK.CITY', 'STOCK_CITY')
			->addSelect('STOCK.ADDRESS', 'STOCK_ADDRESS');

		$query = $ob->getQuery();
		$users = $ob->exec();
		$stocks = [];

		while ($itm = $users->fetch())
		{
			if (!isset($stocks[$itm['PRODUCT_ID']]))
			{
				$stocks[$itm['PRODUCT_ID']] = [
					'PRODUCT_ID' => $itm['PRODUCT_ID'],
					'PRODUCT_NAME' => $itm['PRODUCT_NAME'],
					'SECTION_NAME' => $itm['SECTION_NAME'],
					'PRODUCT_PRICE' => $itm['PRODUCT_PRICE']
				];
			}
			$stocks[$itm['PRODUCT_ID']]['STOCKS'][] = 'г. ' . $itm['STOCK_CITY'] . ' ' . $itm['STOCK_ADDRESS'];
		}

		foreach ($stocks as $stock)
		{
			$result['result']['items'][] = [
				'ID' => $stock['PRODUCT_ID'],
				'PRODUCT_NAME' => $stock['PRODUCT_NAME'],
				'SECTION_NAME' => $stock['SECTION_NAME'],
				'PRODUCT_PRICE' => $stock['PRODUCT_PRICE'],
				'STOCKS' => '<p>' . implode('</p><p>', $stock['STOCKS']) . '</p>'
			];
		}

		$arQuery = [];
		if (isset($_SESSION['dbQuery']) && $_SESSION['dbQuery'])
		{
			$arQuery = $_SESSION['dbQuery'];
			unset($_SESSION['dbQuery']);
		}
		$arQuery[] = $query;
		ViewManager::show('query', ['query' => $arQuery]);
		ViewManager::show('table', $result);
		ViewManager::show('footer');
		return '';
	}

	public static function add()
	{
		ViewManager::show('header', ['title' => 'Добавление товара']);

		$query = [];

		$ob = SectionTable::query()
			->addSelect('ID', 'SECTION_ID')
			->addSelect('NAME', 'SECTION_NAME');

		$query[] = $ob->getQuery();
		$sectionObj = $ob->exec();
		$sections = [];

		while ($itm = $sectionObj->fetch())
		{
			$sections[] = [
				'id' => $itm['SECTION_ID'],
				'name' => $itm['SECTION_NAME']
			];
		}

		$ob = StockTable::query()
			->addSelect('ID', 'STOCK_ID')
			->addSelect('CITY', 'STOCK_CITY')
			->addSelect('ADDRESS', 'STOCK_ADDRESS');

		$query[] = $ob->getQuery();
		$stocksObj = $ob->exec();
		$stocks = [];

		while ($itm = $stocksObj->fetch())
		{
			$stocks[] = [
				'id' => $itm['STOCK_ID'],
				'name' => 'г. ' . $itm['STOCK_CITY'] . ' ' . $itm['STOCK_ADDRESS']
			];
		}

		$result['result'] = [
			'action' => '/product/add/',
			'items' => [
				[
					'name' => 'Название',
					'code' => 'NAME',
					'type' => 'text',
					'value' => '',
					'list_values' => []
				],
				[
					'name' => 'Категория',
					'code' => 'SECTION',
					'type' => 'list',
					'value' => '',
					'list_values' => $sections
				],
				[
					'name' => 'Цена',
					'code' => 'PRICE',
					'type' => 'text',
					'value' => '',
					'list_values' => []
				],
				[
					'name' => 'Склады',
					'code' => 'STOCK',
					'type' => 'multiple_list',
					'value' => '',
					'list_values' => $stocks
				],
			],
		];
		ViewManager::show('query', ['query' => $query]);
		ViewManager::show('record', $result);

		ViewManager::show('footer');
		return '';
	}

	public static function addAction()
	{
		if (!$_POST['STOCK'])
		{
			header('Location: /product/');
			die();
		}

		$productId = ProductTable::add([
			'NAME' => $_POST['NAME'],
			'SECTION_ID' => $_POST['SECTION'],
			'PRICE' => $_POST['PRICE']
		]);

		foreach ($_POST['STOCK'] as $item)
		{
			ProductTable::add([
				'PRODUCT_ID' => $productId,
				'STOCK_ID' => $item
			], ProductTable::PRODUCT_STOCK_TABLE);
		}

		header('Location: /product/');
		die();
	}

	public static function update()
	{
		ViewManager::show('header', ['title' => 'Заказы']);
		ViewManager::show('footer');
		return '';
	}

	public static function updateAction()
	{

	}

	public static function deleteAction()
	{

	}
}