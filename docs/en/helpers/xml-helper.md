[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/xml-helper.md) | [RU](../../ru/helpers/xml-helper.md)
#  XmlHelper

`XmlHelper` is a utility class for working with XML in the Scaleum Framework.

##  Purpose

- Validation of an XML string

##  Main Methods

| Method | Purpose |
|:------|:--------|
| `isXml(string $str)` | Checks if a string is valid XML |

##  Usage Examples

###  XML Validation

```php
$isValidXml = XmlHelper::isXml('<note><to>User</to><from>Admin</from><body>Hello</body></note>');
```
[Back to Contents](../index.md)