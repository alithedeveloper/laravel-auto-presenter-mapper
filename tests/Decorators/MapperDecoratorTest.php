<?php

/*
 * This file is part of Laravel Auto Presenter.
 *
 * (c) Shawn McCool <shawn@heybigname.com>
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace McCool\Tests\Decorators;

use GrahamCampbell\TestBench\AbstractTestCase;
use Illuminate\Contracts\Container\Container;
use McCool\LaravelAutoPresenter\AutoPresenter;
use Mockery;
use RickSelby\LaravelAutoPresenterMapper\AutoPresenterMapper;
use RickSelby\LaravelAutoPresenterMapper\Decorators\MapperDecorator;
use RickSelby\Tests\Stubs\MappedStub;
use RickSelby\Tests\Stubs\MappedStubPresenter;
use RickSelby\Tests\Stubs\UnmappedStub;

class MapperDecoratorTest extends AbstractTestCase
{
    /** @var  MapperDecorator */
    private $decorator;

    /**
     * @before
     */
    public function setUpProperties()
    {
        $this->decorator = new MapperDecorator(
            Mockery::mock(AutoPresenter::class),
            Mockery::mock(AutoPresenterMapper::class),
            Mockery::mock(Container::class)
        );

    }

    public function testCanDecoratePresenter()
    {
        $mappedStub = new MappedStub();
        $this->decorator->getAutoPresenterMapper()->shouldReceive('hasPresenter')->once()
            ->with($mappedStub)->andReturn(true);
        $this->assertTrue($this->decorator->canDecorate($mappedStub));

        $unmappedStub = new UnmappedStub();
        $this->decorator->getAutoPresenterMapper()->shouldReceive('hasPresenter')->once()
            ->with($unmappedStub)->andReturn(false);
        $this->assertFalse($this->decorator->canDecorate($unmappedStub));
    }

    public function testShouldHandleRelations()
    {
        $model = Mockery::mock(MappedStub::class);
        $relations = ['blah'];

        $this->decorator->getAutoPresenter()->shouldReceive('decorate')->once()
            ->with($relations[0])->andReturn('foo');

        $this->decorator->getAutoPresenterMapper()->shouldReceive('getPresenter')->once()
            ->andReturn(MappedStubPresenter::class);

        $model->shouldReceive('getRelations')->once()->andReturn($relations);
        $model->shouldReceive('setRelation')->once()->with(0, 'foo');
        $this->decorator->getContainer()->shouldReceive('make')->once()->andReturn(new MappedStubPresenter($model));

        $this->decorator->decorate($model);
    }
}
