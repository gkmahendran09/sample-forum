@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @foreach($threads as $thread)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="level">
                                <h4 class="flex">
                                    <a href="{{$thread->path()}}">
                                        {{ $thread->title }}
                                    </a>
                                </h4>

                                <a href="{{ $thread->path() }}">
                                    <strong>{{ $thread->replies_count }} {{ str_plural('reply', $thread->replies_count) }}</strong>
                                </a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <article>
                                <div style="margin-bottom: 15px;">
                                    <small>by <a
                                                href="/profiles/{{$thread->creator->name}}">{{ $thread->creator->name }}</a>, {{ $thread->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="body">{{ $thread->body }}</div>
                            </article>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
