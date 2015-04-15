from vegadns.api import endpoint, app
from vegadns.api.endpoints import domain, domains, record, records


if __name__ == '__main__':
    app.run(debug=True)
