from flask import Flask, abort, redirect, url_for, jsonify
app = Flask(__name__)

@app.route('/', methods=['GET'])
def index():
    return redirect(url_for('domains'))

@app.route('/v1/domains', methods=['GET', 'POST', 'PUT', 'DELETE'])
def domains():
    domain_list = [{'id': 1, 'name': 'vegadns.org', 'owner_id': 0}]
    return jsonify(status='ok', domains=domain_list)

if __name__ == '__main__':
    app.run(debug=True)
