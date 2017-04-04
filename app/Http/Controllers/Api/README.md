## API Error Codes & Responses

### HTTP Status Code Types

We use standard HTTP status codes in our app. The ones we use can be split into four categories.
Any client logic can easily know the overall status of an HTTP request based on its code.

| Code | Type |
| --- | --- |
| 2xx | Success  |
| 3xx | Redirection  |
| 4xx | Client error  |
| 5xx | Server error  |

### HTTP Status Codes

| Code | Text | Description |
| --- | --- | --- |
| 200 | OK | The request was a success. Good stuff. |
| 201 | Create | A new resource has been created. |
| 301 | Moved Permanently | This resource has been permanently moved to a new location. |
| 302 | Found | This resource has been temporarily moved to a new location. |
| 304 | Not Modified | This resource hasn't changed since your last request. |
| 400 | Bad Request | Invalid request, probably missing required parameters. |
| 401 | Unauthorized | You're not authenticated but need to be. |
| 402 | Request Failed | The parameters were valid but the request failed. |
| 403 | Forbidden | You are authenticated but don't have access/permission to perform the action you are trying. |
| 404 | Not Found | The requested resource, endpoint, thing doesn't exist. |
| 405 | Method Not Allowed | HTTP method not allowed here. |
| 406 | Not Acceptable | We can't give you a result based on the request supplied. |
| 410 | Gone | This resource no longer exists. Don't be sad. |
| 415 | Unsupported Media Type | The media type supplied is not supported. |
| 422 | Unprocessable Entity | The parameters were valid but validation failed. |
| 429 | Too Many Requests | Too many requests to the API over a short period. Back off mate, exponentially if possible. |
| 500 | Internal Server Error | Woops, something has broken. |
| 502 | Bad Gateway | The service is down or being upgraded. |
| 503 | Service Unavailable | We are overloaded with requests. |
| 504 | Gateway Timeout | The request couldn’t be serviced due to some failure causing unnecessarily long waiting times. |

### Error codes

#### Errors

#### Validation

##### ERR-VALIDATION-REQUIRED

##### ERR-VALIDATION-FILE

##### ERR-VALIDATION-MIMES

##### ERR-VALIDATION-DIMENSIONS



> etc.