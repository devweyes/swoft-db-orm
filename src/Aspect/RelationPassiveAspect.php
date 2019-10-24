<?php

namespace Swoft\Orm\Aspect;

use Swoft\Orm\Exception\RelationException;
use Swoft\Aop\Annotation\Mapping\After;
use Swoft\Aop\Annotation\Mapping\AfterReturning;
use Swoft\Aop\Annotation\Mapping\AfterThrowing;
use Swoft\Aop\Annotation\Mapping\Around;
use Swoft\Aop\Annotation\Mapping\Aspect;
use Swoft\Aop\Annotation\Mapping\Before;
use Swoft\Aop\Annotation\Mapping\PointAnnotation;
use Swoft\Aop\Annotation\Mapping\PointBean;
use Swoft\Aop\Point\JoinPoint;
use Swoft\Aop\Point\ProceedingJoinPoint;
use Swoft\Orm\Annotation\Mapping\RelationPassive;
use Swoft\Db\Eloquent\Model;

/**
 * Class RelationPassiveAspect
 *
 * @since 2.0
 *
 * @Aspect(order=1)
 *
 * @PointAnnotation(include={RelationPassive::class})
 */
class RelationPassiveAspect
{
    /**
     * @Before()
     */
    public function before()
    {
        // before
    }

    /**
     * @After()
     */
    public function after()
    {
        // After
    }

    /**
     * @AfterReturning()
     *
     * @param JoinPoint $joinPoint
     *
     * @return mixed
     */
    public function afterReturn(JoinPoint $joinPoint)
    {
        $method = $joinPoint->getMethod();
        $class = $joinPoint->getTarget();
        $key = strtolower(str_replace('get', '', $method));
        if (!$class instanceof Model) {
            throw new RelationException(sprintf(
                '%s::%s not instanceof {Swoft\Db\Eloquent\Model}',
                $this->getClassName(),
                $key
            ));
        }
        return $class->getRelationResults($key);
    }

    /**
     * @Around()
     *
     * @param ProceedingJoinPoint $proceedingJoinPoint
     *
     * @return mixed
     */
    public function around(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Before around
        $result = $proceedingJoinPoint->proceed();
        // After around

        return $result;
    }

    /**
     * @param \Throwable $throwable
     *
     * @AfterThrowing()
     */
    public function afterThrowing(\Throwable $throwable)
    {
        // afterThrowing
    }
}
