# Laravel Framework REST API

Пакеь разработан для удобства работы с API запросами. На данный момент этот модуль предоставляет всего лишь
контроллер, с набором методов, которые помогут организовать работу с AJAX запросами.

## Установка

```
composer require kodicms/laravel-api
```

### Добавить фасад в алиасы
<pre>
'RouteAPI' => KodiCMS\API\RouteApiFacade::class,
</pre>

### Внести изменения в файл `app\Exceptions\Handler.php`

```
	...

	use KodiCMS\API\Http\Response as APIResponse;
	use KodiCMS\API\Exceptions\Exception as APIException;

	...

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Exception               $e
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, \Exception $e)
	{
		if ($request->ajax() OR ($e instanceof APIException)) {
			return (new APIResponse(config('app.debug')))
				->createExceptionResponse($e);
		}

		...
	}
```

# Описание

## Контроллер ##
Контроллер необходимо наследовать от `KodiCMS\API\Http\Controllers\Controller`, после чего он начнет возвращать
все ответы (ошибки и т.д.) в определенном формате с возможностью выбора типа ответа (`json`, `xml`, `yaml`):

**Пример ответа:**
<pre>
{
	code: 200,
	content: '....',
	type: 'content',
	method: '...'
}
</pre>


**Коды ответов:**

 * `200` - ok
 * `110` - не передан обязательный параметр в запросе
 * `120` - ошибка валидации
 * `130` - неизвестная ошибка
 * `140` - ошибка токена
 * `150` - Попытка в модели установить значение защищенного поля
 * `220` - ошибка прав доступа
 * `403` - Неавторизован
 * `404` - страница не найдена

**Тип ответа**

 * `error` - ошибка
 * `content` - ответ
 * `redirect` - редирект

**Пример ответов с различным кодом в `json` формате**

```
// 200
{
	code: 200,
	content: '....',
	type: 'content',
	method: '...'
}

// 200/Redirect
{
	code: 200,
	targetUrl: '....',
	content: '....',
	type: 'redirect',
	method: '...'
}

// 110
{
	code: 110,
	type: 'error',
	failed_rules: {...}, // поля, которые не прошли валидацию с текстом ошибки
	message: ... // текст ошибки
	...
}

// 120
{
	code: 120,
	type: 'error',
	errors: {...}, // текст ошибок
	failed_rules: {...} поля, которые не прошли валидацию с текстом ошибки
	...
}

// Другие ошибки
{
	type: 'error',
	message: ... // текст ошибки
	...
}
```

## Полезные методы

 * `getParameter($key, $default = NULL, $isRequired = false)` - получение параметра переданного в запросе
 * `getRequiredParameter($key, $rules = true)` - получение обязательного параметра, Вторым параметром можно указать правила валидации (`true == 'required'`), выбрасывает ошибку 110
 * `setMessage($message)` - передача сообщения в ответ
 * `setErrors(array $errors)` - передача ошибок в ответ
 * `setContent($data)` - передача данных в ответ. Если передается `view`, то он будет преобразован в HTML

 ## Полезные параметры

  * `jsonResponse` - данные, которые будут переданы в ответ и преобразованы в JSON
  * `requiredFields` - ожидаемые поля в виде `['action' => ['param1, 'param2']]`

## Роутер
Для модуля API создан фассад `RouteAPI` который используется для добавления API роутов, который автоматически
добавляет к новому маршруту возможность выбора типа ответа `json`, `xml`, `yaml`.

**API запрос можно выполнить следующим образом:**

- *site.com/api.refresh.key* => `json`,
- *site.com/api.refresh.key.json* => `json`,
- *site.com/api.refresh.key.xml* => `xml`,
- *site.com/api.refresh.key.yaml* => `yaml`

**Пример**
```
RouteAPI::post('refresh.key', ['as' => 'api.refresh.key', 'uses' => 'API\KeysController@postRefresh']);

// Что равнозначно
Route::post('api.refresh.key{type?}', ['as' => 'api.refresh.key', 'uses' => 'API\KeysController@postRefresh'])->where('type', '\.[a-z]');
```


----------

**Пример контроллера с возвращением данных**

```
use KodiCMS\API\Http\Controllers\Controller;
class PageController extends Controller
{
	public function getSearch()
	{
		$query = $this->getRequiredParameter('search');

		...

		$this->setContent(view('pages.children'));
	}
}

// return JsonResponse

{
	code: 200,
	content: 'html data',
	method: 'get',
	type: 'content'
}
```

**Пример контроллера с возвратом данных в произвольной форме**
```
use KodiCMS\API\Http\Controllers\Controller;

class MessageController extends Controller
{
	public function sendMessage()
	{
		$message = $this->getRequiredParameter('message');

		...

		$this->testParam = '...';
		// или
		$this->jsonResponse['testParam'] = '...';

		$this->setMessage($message);
	}
}


// return JsonResponse

{
	code: 200,
	content: null,
	testParam: '...'
	type: 'content',
	method: 'post',
	message: 'message text'
}
```


**Пример контроллера с редиректом**
```
use KodiCMS\API\Http\Controllers\Controller;
class MessageController extends Controller
{
	public function sendMessage()
	{
		$message = $this->getRequiredParameter('message');

		...

		return redirect('...');
	}
}

// return JsonResponse

{
	code: 200,
	targetUrl: '....',
	content: '....',
	method: 'post',
	type: 'redirect'
}
```

**Пример контроллера с выводом HTML**
```
use KodiCMS\API\Http\Controllers\Controller;
class MessageController extends Controller
{
	public function sendMessage()
	{
		$message = $this->getRequiredParameter('message');

		...

		return view('.....');
	}
}

// return JsonResponse

'html content'
```

----------

### Исключения

API модуль имеет свой класс исключений `KodiCMS\API\Exceptions\Exception` от которого лучше всего наследовать
исключения, которые используются в API контроллерах. Данный класс содержит метод `responseArray`, в котором содержится
список параметров, которые попадут в `Response`

```
/**
 * @var int
 */
protected $code = Response::ERROR_UNKNOWN;

public function responseArray()
{
	return [
		'code' => $this->getCode(),
		'type' => Response::TYPE_ERROR,
		'message' => $this->getMessage(),
	];
}
```

Пример кастомного класса исключений:

```
use KodiCMS\API\Exceptions\Exception;
use KodiCMS\API\Http\Response;

class ValidationException extends Exception
{
	/**
	 * @var int
	 */
	protected $code = Response::ERROR_VALIDATION;

	public function getFailedRules()
	{
		...
	}

	public function getErrorMessages()
	{
		...
	}

	/**
	 * @return array
	 */
	public function responseArray()
	{
		$data = parent::responseArray();
		$data['failed_rules'] = $this->getFailedRules();
		$data['errors'] = $this->getErrorMessages();

		return $data;
	}
}
```