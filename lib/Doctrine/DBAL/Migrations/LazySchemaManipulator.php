<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Migrations;

class LazySchemaManipulator
{
    private $proxyFactory;

    private $originalSchemaManipulator;

    public function __construct($proxyFactory, $originalSchemaManipulator)
    {
        $this->proxyFactory = $proxyFactory;
        $this->originalSchemaManipulator = $originalSchemaManipulator;
    }

    public function createFromSchema()
    {
        return $this->proxyFactory->createProxy(
            Schema::class,
            function (& $wrappedObject, $proxy, $method, array $parameters, & $initializer) {
                $initializer   = null;
                $wrappedObject = $this->originalSchemaManipulator->createFromSchema();

                return true;
            }
        );
    }

    public function createToSchema($fromSchema)
    {
        if ($fromSchema instanceof LazyLoadingInterface && ! $fromSchema->isProxyInitialized()) {
            return $this->createFromSchema();
        }

        return $this->originalSchemaManipulator->createToSchema($fromSchema);
    }

    public function addSqlToSchema($fromSchema, $toSchema)
    {
        if (
            $fromSchema instanceof LazyLoadingInterface
            && $toSchema instanceof LazyLoadingInterface
            && ! $fromSchema->isProxyInitialized()
            && ! $toSchema->isProxyInitialized()
        ) {
            return;
        }

        return $this->originalSchemaManipulator->addSqlToSchema($fromSchema, $toSchema);
    }
}
