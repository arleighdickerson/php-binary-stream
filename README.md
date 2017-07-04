# php-binary-stream
Stream binary data over websockets with a php backend

Long story short: with some monkey patching on top of ratchet, you can stream binary data over websockets with PHP.

PHP does not appear to be efficient enough to actually use this for real-time data. Using [React](http://reactphp.org)'s stream_select event loop I was able to get some audio streaming between browsers, but the skips were longer than the sound. This may or may not be the case with the other (libevent, libev) React loop implementations as they are supposed to be faster.

However, running with HHVM (with the same stream_select loop implementation) resulted in no skipping at all and appeared to be fast enough to be useful for real-time streams.

TODO
===========================

- Use [amphp](https://github.com/amphp/websocket)'s websocket server implementation because it doesn't ~~suck~~ require a monkeypatch to be useful.
- Add some build instructions for the js
