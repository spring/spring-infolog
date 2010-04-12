# -*- coding: utf-8 -*-
from bottle import route,request
from siteglobals import env, db, config
from utils import *
from backend import Crash

@route('/list', method='GET')
def output():
	try:
		session = db.sessionmaker()
		upload_dir = config.get('site','uploads')
		crashs = session.query( Crash ).all()
		ret = env.get_template('list.html').render( crashs=crashs )
		session.close()
		return ret

	except Exception, m:
		return env.get_template('error.html').render(err_msg=str(m))

