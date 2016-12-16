=========
Class Str
=========

Класс для работы со строками.

------------

ord
---
Описание
    ::

        int ord ( string $char )

    Возвращает код символа в кодировке UTF-8.
    Аналог функции `ord <http://php.net/ord>`_, работающей с ASCII-кодировкой.

Список параметров
    :$char: Символ в кодировке UTF-8.

Возвращаемые значения
    Возвращает unicode-код символа в виде целого числа.


chr
---

Описание
    ::

        string chr ( int $code )

    Возвращает символ в кодировке UTF-8 по его коду.
    Аналог функции `chr <http://php.net/chr>`_, работающей с ASCII-кодировкой.

Список параметров
    :$code: Unicode-код символа.

Возвращаемые значения
    Возвращает символ по его коду.


filter
------

Описание
    ::

        string filter ( string $string [, int $options = Str::FILTER_TEXT ] )

    Фильтрует специальные символы в строке в кодировке UTF-8.
    К специальным символам относятся все символы, за исключением основной латиницы (20-7F),
    части кириллицы (400-45F), символов табуляции (09) и перевода строки (0A).

Список параметров
    :$string: Фильтруемая строка.
    :$options:
        Принимает одно или сумму из следующих значений:

        - **Str::FILTER_TEXT** - (по умолчанию) удаляет все специальные символы.
        - **Str::FILTER_HTML** - удаляет все непечатные базовые символы, замещает все специальные символы на их html-сущности.
        - **Str::FILTER_CODE** - замещает все специальные символы на их шестнадцатеричный код в формате ``[%0000]``. Используется для отладки.
        - **Str::FILTER_PUNCTUATION** - заменяет все возможные виды дефисов/тире и кавычек на ``-`` (2D) и ``"`` (22) соответсвтенно.
        - **Str::FILTER_SPACE** - заменяет все последовательности пробельных символов на пробел (x20).

        Фильтры Str::FILTER_TEXT и Str::FILTER_HTML можно комбинировать с фильтрами Str::FILTER_PUNCTUATION и Str::FILTER_SPACE.

Возвращаемые значения
    Отфильтрованная строка.

Расширяемость
    Статическое свойство класса **$filterCodeFormat** равно ``[%%%'04X]``
    и отвечает за формат вывода специальных символов с опцией Str::FILTER_CODE.
    Его можно переопределить на любой другой формат, совместимый с `sprintf <http://php.net/sprintf>`_.


getSlug
-------

Описание
    ::

        string getSlug ( string $string [, string $characters = '' [, $placeholder = '-' ] ] )

    Транслитерирует строку и заменяет специальные символы на **$placeholder**.

Список параметров
    :$string: Фильтруемая строка.
    :$characters: Дополнительный список символов, которые не будут заменены. По умолчанию пуст.
    :$placeholder: Символ, на который заменяются все специальные символы. По умолчанию знак ``-`` (2D).

Возвращаемые значения
    Отфильтрованная строка.

Расширяемость
    По умолчанию транслитерация происходит только для букв русского алфавита.
    Изменить схему замены или добавить новые траслитерируемые символы можно переопределив
    статическое свойство **$slugTransliteration**.


getRandomString
---------------

Описание
    ::

        string getRandomString ( [ int $length = 32 [, string $characters = 'qwertyuiopasdfghjklzxcvbnm0123456789' ] ] )

    Генерирует псевдослучайную строку.
    Для генерации используется функция `mt_rand <http://php.net/mt_rand>`_.

Список параметров
    :$length: Длина строки. По умолчанию 32.
    :$characters:
        Список символов, из которых будет состоять строка.
        По умолчанию символы латинского алфавита в нижнем регистре и цифры.

Возвращаемые значения
    Псевдослучайная строка.


isUrl
-----

*todo*


isEmail
-------

*todo*


isHash
------

*todo*


pack
----

*todo*


unpack
------

*todo*


pad
---

*todo*


convertCase
-----------

Описание
    ::

        string convertCase ( string $string , int $convention )

    Перевод строки в соответствии с заданым `стандартом <https://en.wikipedia.org/wiki/Naming_convention_(programming)>`_.

Список параметров
    :$string: Строка для конвертации.
    :$convention:
        Соглашение, на основе которого будет происходить конвертация.
        Принимает одно из следующих значений:

        - **Str::CASE_CAMEL_LOWER** - `lower camel case <https://en.wikipedia.org/wiki/Camel_case>`_.
        - **Str::CASE_CAMEL_UPPER** - upper camel case (pascal case).
        - **Str::CASE_SNAKE_LOWER** - `snake case <https://en.wikipedia.org/wiki/Snake_case>`_.
        - **Str::CASE_SNAKE_UPPER** - screaming snake case.
        - **Str::CASE_KEBAB_LOWER** - `kebab case <https://en.wikipedia.org/wiki/Letter_case#Special_case_styles>`_ (lisp case).
        - **Str::CASE_KEBAB_UPPER** - upper kebab case.

Возвращаемые значения
    Сконвертированная строка.


getShortClassName
-----------------

*todo*

