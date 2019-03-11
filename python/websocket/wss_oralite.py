#!/usr/bin/env python

# WS server pour gérer le changement d'auteur dans une oralité croisée

import asyncio
import json
import logging
import websockets

logging.basicConfig()

STATE = {'auteur': 'a','slide':'0'}

USERS = set()

def state_event():
    return json.dumps({'type': 'state', **STATE})

def users_event():
    return json.dumps({'type': 'users', 'count': len(USERS)})

async def notify_state():
    if USERS:       # asyncio.wait doesn't accept an empty list
        message = state_event()
        await asyncio.wait([user.send(message) for user in USERS])

async def notify_users():
    if USERS:       # asyncio.wait doesn't accept an empty list
        message = users_event()
        await asyncio.wait([user.send(message) for user in USERS])

async def register(websocket):
    USERS.add(websocket)
    await notify_users()

async def unregister(websocket):
    USERS.remove(websocket)
    await notify_users()

async def counter(websocket, path):
    # register(websocket) sends user_event() to websocket
    await register(websocket)
    try:
        await websocket.send(state_event())
        async for message in websocket:
            data = json.loads(message)
            if data['action'] == 'auteur':
                STATE['auteur'] = data['a']
                STATE['slide'] = data['s']
                await notify_state()
            elif data['action'] == 'navigue':
                STATE['slide'] = data['s']
                await notify_state()
            else:
                logging.error(
                    "unsupported event: {}", data)
    finally:
        await unregister(websocket)

asyncio.get_event_loop().run_until_complete(
    websockets.serve(counter, 'gapai.univ-paris8.fr', 7272))
asyncio.get_event_loop().run_forever()