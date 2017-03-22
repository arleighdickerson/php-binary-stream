<?php

namespace arls\binarystream;

class StreamHelpers {
    public static function pipe($source, $dest, array $options = ['end' => false]) {
        if (!$source->isReadable()) {
            return $dest;
        }
        // destination not writable => just pause() source
        if (!$dest->isWritable()) {
            //$source->pause();
            //return $dest;
        }
        $dest->emit('pipe', [$source]);
        // forward all source data events as $dest->write()
        $source->on('data', $dataer = function ($data) use ($source, $dest) {
            $feedMore = $dest->write($data);
            if (false === $feedMore) {
                $source->pause();
            }
        });
        $dest->on('close', function () use ($source, $dataer) {
            $source->removeListener('data', $dataer);
            $source->pause();
        });
        // forward destination drain as $source->resume()
        $dest->on('drain', $drainer = function () use ($source) {
            $source->resume();
        });
        $source->on('close', function () use ($dest, $drainer) {
            $dest->removeListener('drain', $drainer);
        });
        // forward end event from source as $dest->end()
        $end = isset($options['end']) ? $options['end'] : true;
        if ($end) {
            $source->on('end', $ender = function () use ($dest) {
                $dest->end();
            });
            $dest->on('close', function () use ($source, $ender) {
                $source->removeListener('end', $ender);
            });
        }
        return $dest;
    }

    public static function forwardEvents($source, $target, array $events) {
        foreach ($events as $event) {
            $source->on($event, function () use ($event, $target) {
                $target->emit($event, func_get_args());
            });
        }
    }

    public static function writeToFile($stream, $filename) {
        $fp = fopen($filename, 'w');
        $stream->on('data', function ($data) use ($fp) {
            fwrite($fp, $data);
            fflush($fp);
        });
        $stream->on('end', function () use ($fp) {
            fclose($fp);
        });
        return $stream;
    }
}
