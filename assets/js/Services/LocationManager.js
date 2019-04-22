export default class LocationManager {
  constructor(locations) {
    this.locations = locations;
  }

  getCountries() {
    return this.locations.map(country => country);
  }

  getStates() {
    return this.locations.flatMap(country => (
      country.states.map(state => (state))
    ));
  }

  getCities() {
    return this.locations.flatMap(country => (
      country.states.flatMap(state => (
        state.cities.map(city => city)
      ))
    ));
  }

  getStatesByCountryId(countryId) {
    return this.locations.filter(country => (country.id === countryId))
      .flatMap(country => country.states);
  }

  getCitiesByState(stateId) {
    return this.getStates().filter(state => (Number(state.id) === Number(stateId)))
      .flatMap(state => state.cities);
  }

  getCountryByCityId(cityId) {
    return this.locations.find(country => (country.states.find(
      state => (state.cities.find(city => (Number(city.id) === Number(cityId)))),
    )));
  }

  getCountryByStateId(stateId) {
    return this.locations.find(country => (country.states.find(
      state => (state.id === Number(stateId)),
    )));
  }

  getStateByCityId(cityId) {
    return this.getStates().find(state => (state.cities.some(
      city => (city.id === Number(cityId)),
    )));
  }

  getCountryById(countryId) {
    return this.locations.find(country => country.id === Number(countryId));
  }

  getStateById(stateId) {
    return this.getStates().find(state => (state.id === Number(stateId)));
  }

  getCityById(cityId) {
    return this.getCities().find(city => (city.id === Number(cityId)));
  }
}
