<?php

declare(strict_types=1);

namespace Drupal\obw_utilities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * CDN domain constraint validator.
 */
class AlterCdnDomainConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($domain, Constraint $constraint) {
    if (!$constraint instanceof AlterCdnDomainConstraint) {
      throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\AlterCdnDomain');
    }

    if ($domain === NULL) {
      return;
    }

    if (!static::isValidCdnDomain($domain)) {
      $this->context->buildViolation($constraint->message)
        ->setParameter('%domain', $domain)
        ->setInvalidValue($domain)
        ->addViolation();
    }
  }

  /**
   * Validates the given CDN domain.
   *
   * @param string $domain
   *   A domain as expected by the CDN module: an "authority" in RFC3986.
   *
   * @return bool
   */
  protected static function isValidCdnDomain(string $domain): bool {
    // Add a scheme so that we have a parseable URL.
    $url = 'https://' . $domain;
    $components = parse_url($url);
    #$forbidden_components = ['path', 'query', 'fragment'];
    $forbidden_components = ['query', 'fragment'];
    return $components === FALSE ? FALSE : empty(array_intersect($forbidden_components, array_keys($components)));
  }

}
