[Back to Index](index.md)

# Contributing to FEAST

FEAST was crafted with careful attention to the structure and quality of the code. These guidelines reflect the
architectural guidance of FEAST.

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”, “SHOULD NOT”, “RECOMMENDED”, “MAY”, and
“OPTIONAL” in this document are to be interpreted as described in [RFC 2119](http://tools.ietf.org/html/rfc2119).

[Coding Standards](#coding-standards)

[Breaking Backwards Compatibility](#breaking-backwards-compatibility)

[Documentation Standards](#documentation-standards)

[Testing Standards](#testing-standards)

[Quality Standards](#quality-standards)

[Further Reading](#further-reading)

## Coding Standards

FEAST follows the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard. In addition, the following rules MUST
be adhered to. Any pull request not adhering to these standards will be declined.

1. All variable names MUST be in camelCase.
2. All constants MUST be in SNAKE_CASE.
3. All methods MUST declare a return type. Mixed SHOULD NOT be used.
4. All method parameters MUST declare their types. Mixed SHOULD NOT be used.
5. All class properties MUST declare their types. Mixed SHOULD NOT be used.
6. All methods MUST have a clear name that defines what the method does. Any user of the framework should be able to
   read nothing but signatures and know what actions will happen.

## Breaking Backwards Compatibility

The maintainers of FEAST recognize that sometimes a feature is not well-thought-out when it is first implemented and
must be removed. In those instances, a feature using public methods MAY only be removed if the following is true:

1. The functionality was deprecated in a MINOR release (By adding a call to `trigger_error` with the type passed in
   as `E_USER_DEPRECATED`).
2. The functionality removal is introduced in a MAJOR release.
3. The change MUST be noted in the Release Notes

Often, rather than removing functionality, a backwards incompatible change may be necessary. The maintainers of FEAST do
not wish for the framework to become stale, so those changes are encouraged. In the case of these types of changes, the
following MUST be true:

1. The functionality change MUST be introduced in a MAJOR release,
2. The change MUST be noted in the Release Notes.

In other times, new language features of PHP may be released. In those instances, for a feature to be changed, the 
following MUST be true:

1. The functionality change is taking advantage of features that do not exist in the previous version of PHP.
2. The functionality change is introduced in a MAJOR release.
3. The change MUST be noted in the release notes.

Public method and class names SHOULD NOT be changed. A name MAY only be changed if the following is true:

1. The name change MUST be introduced in a MAJOR release,
2. The change MUST be noted in the Release Notes.

Changes to protected or private methods that do not change the inputs or outputs in public methods do not require any of
the previous rules to apply, and may be performed on PATCH releases.

## Documentation Standards

The following documentation standards apply to all code. These standards MUST be adhered to. Any pull request violating
these standards will be declined.

1. All public methods MUST have a Docblock. Protected and private methods MAY have a Docblock.
2. All public method changes MUST be documented in the appropriate file in the docs folder.

## Testing Standards

The following testing standards apply to all code. These standards MUST be adhered to. Any pull request violating these
standards will be declined.

### Unit Testing

1. All methods MUST have 100% code coverage with [PHPUnit](https://phpunit.de/). If a method cannot be tested, rewrite
   it.
2. Mock objects and simulated objects or functions MAY be used in cases where a deterministic value is needed. Example:
   microtime or file_exists in tests.
3. Unit tests MUST NOT be skipped.
4. If existing code is modified, the tests MUST pass without alteration of the asserts, unless one of the following is
   true.
    1. The original behavior was incorrect, rendering the original assert invalid.
    2. The original behavior was correct, but the new behavior expanded functionality AND the test has been added to,
       requiring modification of the assert. In this instance, ensure the test is passing before modifying for the new
       functionality. Alternatively, copy and paste the entire test and modify the new one.
    3. The behavior has changed, breaking backwards compatibility AND the submitted pull request is for a new MAJOR
       release AND supporting documentation for the change in behavior is submitted.

### Static Type Analysis

1. All code MUST pass [Psalm](https://psalm.dev/) static type analysis with 100% type inference.
2. All Psalm inspections SHOULD pass without suppression. In some instances, due to the dynamic nature of code, it MAY
   be necessary to suppress errors as "false positives".

## Quality Standards

FEAST has strict quality standards. These standards MUST be adhered to. Any pull request violating these standards will
be declined.

1. All methods MUST be clearly documented.
2. All classes MUST only contain methods directly relevant to the class.
3. Methods SHOULD NOT take boolean flags that change what type of data is returned.
4. All methods MUST perform only one action. While this is admittedly subjective, it will be the judgement call of the
   package maintainers. If you are unsure, split methods apart using "chainer" methods which are defined next.
5. When a sequence of events is performed, the usage of "chainer" methods is REQUIRED. Chainer methods are methods that
   only call other methods and (optionally) return a result.

## Arbitration

The package maintainers will make every effort to merge in appropriate pull requests. Pull requests that do not fit the
vision or purpose of the FEAST Framework will be rejected and closed. Pull requests that do not meet these standards,
but serve to improve FEAST will receive feedback from the maintainers or may (at their discretion) be iterated on or
reimplemented by them. If the package maintainers re-implement your idea, credit will be given in the contributors file.
The package maintainers decisions are final and are not subject to appeal.

## Further Reading

The following books were used by the author of the framework as guiding principles.

[Clean Code: A Handbook of Agile Software Craftsmanship](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882)
by Robert C. Martin

[Clean Architecture: A Craftsman's Guide to Software Structure and Design](https://www.amazon.com/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494164)
by Robert C. Martin

[The Clean Coder: A Code of Conduct for Professional Programmers](https://www.amazon.com/Clean-Coder-Conduct-Professional-Programmers/dp/0137081073)
by Robert C. Martin

[Refactoring: Improving the Design of Existing Code 2nd Edition](https://www.amazon.com/Refactoring-Improving-Existing-Addison-Wesley-Signature/dp/0134757599)
by Martin Fowler

[Test Driven Development by Example](https://www.amazon.com/Test-Driven-Development-Kent-Beck/dp/0321146530) by Kent
Beck