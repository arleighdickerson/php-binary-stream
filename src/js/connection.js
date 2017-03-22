const {BinaryClient} = require('./client')
const WebSocket = require('./ReconnectingWebSocket')

export const ws = new WebSocket('ws://assets.aeaweb.dev:8889', undefined, {
  binaryType: 'arraybuffer',
  automaticOpen: false
})

export const client = new BinaryClient(ws)
