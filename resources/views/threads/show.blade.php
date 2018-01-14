@extends('layouts.app')

@section('content')
    <thread-view :initial-replies-count="{{ $thread->replies_count }}" inline-template>
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="level">
                                <h5 class="flex">
                                    <a href="/profiles/{{$thread->creator->name}}">{{ $thread->creator->name }}</a> posted:
                                    {{ $thread->title }}
                                </h5>

                                @can('update', $thread)
                                    <form method="POST" action="{{$thread->path()}}">
                                        {{csrf_field()}}
                                        {{method_field("DELETE")}}
                                        <button class="btn btn-link" type="submit">Delete Thread</button>
                                    </form>
                                @endcan
                            </div>
                        </div>

                        <div class="panel-body">
                            {{ $thread->body }}
                        </div>
                    </div>

                    <replies :data="{{ $thread->replies }}" @removed="repliesCount--"></replies>

                    {{--@foreach($replies as $reply)--}}
                    {{--@include('threads.reply')--}}
                    {{--@endforeach--}}

                    {{--{{ $replies->links() }}--}}

                    @if(auth()->check())
                        <form method="POST" action="{{$thread->path() . '/replies'}}">
                            {{ csrf_field() }}

                            <div class="form-group">
                            <textarea class="form-control" rows="5" name="body"
                                      placeholder="Have something to say?"></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-default">Post</button>
                            </div>
                        </form>
                    @else
                        <p class="text-center">Please <a href="{{ route('login') }}">sign in</a> to participate in this
                            discussion.</p>
                    @endif

                </div>
                <div class="col-md-4">
                    <div class="panel panel-default">

                        <div class="panel-body">
                            <p>
                                This thread was published {{$thread->created_at->diffForHumans()}} by
                                <a href="/profiles/{{$thread->creator->name}}">{{ $thread->creator->name }}</a>, and
                                currently
                                has <span v-text="repliesCount"></span> {{ str_plural('comment', $thread->replies_count) }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </thread-view>
@endsection
