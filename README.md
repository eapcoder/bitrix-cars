<p align="center">
    <img src="https://www.1c-bitrix.ru/images/content_common/logo/1c-bitrix-logo.svg">
    <h1 align="center">Комплексный компонент "Bitrix cars"</h1>
    <br>
</p>

<img src="/components/customs/images/diag.png">

СТРУКТУРА КОМПОНЕНТА
-------------------
```php
/customs/           
    /cars/          основная директория компонента
    /cars.element/  для вывода элементов инфоблока
    /cars.index/    основаня страница
    /cars.section/  для вывода категорий
    /traits/        вспомогательный класс
```

ПРОТЕСТИРОВАНО
------------
PHP 8.2.
1С-Битрикс: Управление сайтом 25.100.500

УСТАНОВКА
------------

```php
$APPLICATION->IncludeComponent(
	"customs:cars", 
	".default", 
	array(
		"IBLOCK_ID" => "5",
		"IBLOCK_TYPE" => "Cars",
		"HIGHLIGHT_BLOCK_ID" => "2",
		"SEF_MODE" => "Y",
		"CUSTOM_PARAMS" => ")))",
		"COMPONENT_TEMPLATE" => ".default",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"SEF_FOLDER" => "/cars/",
		"ENDSTART_TEMPLATE" => "YYYY-MM-DD-HH-MI-SS",
		"SEF_URL_TEMPLATES" => array(
			"section" => "#SECTION_CODE#/",
			"element" => "#SECTION_CODE#/#ELEMENT_CODE#/",
		)
	),
	false
);
```
