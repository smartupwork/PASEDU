@if($type == '1')
<div class="row" id="fullaccess">
  <div class="col-md-8 mx-auto">
      <div class="card">
          <div class="card-header"> Features and Access</div>
          <div class="card-body card-block m-3 featuresaccess">
              <div class="row">
                      <div class="col-md-8">
                          Home Dashboard 
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[]" type="checkbox" value="Home_Dashboard" class="form-check-input" checked id="db1">
                              <label class="form-check-label" for="db1">Yes</label>
                          </div>
                      </div> 
              </div>
              <div class="row">
                      <div class="col-md-8">
                      Stats 
                      </div> 
                      <div class="col-md-4">
                      <div class="form-check">
                          <input name="feature[]" value="Stats" type="checkbox" class="form-check-input" checked id="fstate">
                          <label class="form-check-label" for="fstate">Yes</label>
                          </div>
                      </div> 
              </div>
              <div class="row mt-2">
                      <div class="col-md-8">Student Management</div> 
                      <div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[]" value="Student_Management" type="checkbox" class="form-check-input" checked>
                              <label class="form-check-label" for="fview1">Yes</label>
                              </div>
                          </div>

              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          View 
                      </div>
                  </div> 
                   <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="student1" class="form-check-label ">
                                  <input name="student_radio_view" type="radio" checked value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="student2" class="form-check-label ">
                                  <input name="student_radio_view" type="radio" value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          Download/Request 
                      </div>
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check-inline form-check">
                          <label for="download1" class="form-check-label ">
                              <input name="student_radio_download" type="radio" checked value="Yes" class="form-check-input">Yes 
                          </label>
                          <label for="download2" class="form-check-label ">
                              <input name="student_radio_download" type="radio" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          Add
                      </div>
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check-inline form-check">
                          <label for="add1" class="form-check-label ">
                              <input name="student_radio_add" type="radio" checked value="Yes" class="form-check-input">Yes 
                          </label>
                          <label for="add2" class="form-check-label">
                              <input name="student_radio_add" type="radio" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>
              <div class="row mt-2">
                  <div class="col-md-8">Catalog Management</div> 
                  <div class="col-md-4">
                      <div class="form-check">
                          <input name="feature[]" value="Catalog_Management" type="checkbox" class="form-check-input" checked>
                          <label class="form-check-label" for="fview2">Yes</label>
                      </div>
                  </div> 
              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          View 
                      </div>
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check-inline form-check">
                          <label for="catalog1" class="form-check-label ">
                              <input type="radio" name="catalog_radio_view" checked value="Yes" class="form-check-input">Yes 
                          </label>
                          <label for="catalog2" class="form-check-label ">
                              <input type="radio" name="catalog_radio_view" value="No" class="form-check-input">No
                          </label>
                      </div>                                                                
                  </div> 
              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          Download/Request 
                      </div>
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check-inline form-check">
                          <label for="download1" class="form-check-label ">
                              <input type="radio" name="catalog_radio_download" checked value="Yes" class="form-check-input">Yes 
                          </label>
                          <label for="download2" class="form-check-label ">
                              <input type="radio" name="catalog_radio_download" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          Add
                      </div>
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check-inline form-check">
                          <label for="fadd1" class="form-check-label ">
                              <input type="radio" name="catalog_radio_add" checked value="Yes" class="form-check-input">Yes 
                          </label>
                          <label for="fadd2" class="form-check-label">
                              <input type="radio" name="catalog_radio_add" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>
              <div class="row mt-2">
                  <div class="col-md-8">Hosted Site Management</div> 
                  <div class="col-md-4">
                      <div class="form-check">
                          <input name="feature[]" value="Hosted_Site_Management" type="checkbox" class="form-check-input" checked>
                          <label class="form-check-label" for="fview2">Yes</label>
                      </div>
                  </div> 
              </div>
              <div class="row mt-2">
                  <div class="col-md-8">
                      Marketing Access 
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check">
                          <input name="feature[]" value="Marketing_Access" type="checkbox" class="form-check-input" checked>
                          <label class="form-check-label" for="facce">Yes</label>
                      </div>
                  </div> 
              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          On Demand Collateral  
                      </div> 
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check-inline form-check">
                          <label for="catalog1" class="form-check-label ">
                              <input type="radio" name="marketing_radio_demand" checked value="Yes" class="form-check-input">Yes 
                          </label>
                          <label for="catalog2" class="form-check-label ">
                              <input type="radio" name="marketing_radio_demand" value="No" class="form-check-input">No
                          </label>
                      </div>  
                  </div> 
              </div>
              <div class="row">
                  <div class="col-md-8">
                      <div class="ml-4">
                          Request Collateral 
                      </div> 
                  </div> 
                  <div class="col-md-4">
                      <div class="form-check-inline form-check">
                          <label for="request1" class="form-check-label ">
                              <input type="radio" name="marketing_radio_request" checked value="Yes" class="form-check-input">Yes 
                          </label>
                          <label for="request2" class="form-check-label ">
                              <input type="radio" name="marketing_radio_request" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>

          </div>
      </div> 
  </div>
</div>
@elseif($type == '2')
<div class="row" id="accountsupport">
      <div class="col-md-8 mx-auto">
          <div class="card">
              <div class="card-header"> Features and Access</div>
              <div class="card-body card-block m-3 featuresaccess">
                  <div class="row">
                          <div class="col-md-8">
                              Home Dashboard 
                          </div> 
                          <div class="col-md-4">
                              <div class="form-check">
                                  <input name="feature[]" value="Home_Dashboard" type="checkbox" class="form-check-input" checked id="exampleCheck1">
                                  <label class="form-check-label" for="exampleCheck1">Yes</label>
                              </div>
                          </div> 
                  </div>
                  <div class="row">
                          <div class="col-md-8">
                          Stats 
                          </div> 
                          <div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[]" value="Stats" type="checkbox" class="form-check-input" checked>
                              <label class="form-check-label" for="exampleCheck1">Yes</label>
                              </div>
                          </div> 
                  </div>
                  <div class="row mt-2">
                          <div class="col-md-8">Student Management</div> 
                          <div class="col-md-4">
                              <div class="form-check">
                          <input name="feature[]" value="Student_Management" type="checkbox" class="form-check-input" checked>
                          <label class="form-check-label" for="exampleCheck1">Yes</label>
                          </div>
                          </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              View 
                          </div>
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="student1" class="form-check-label ">
                                  <input type="radio" name="student_radio_view" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="student2" class="form-check-label ">
                                  <input type="radio" name="student_radio_view" checked value="No" class="form-check-input">No
                              </label>
                          </div>                                                                    
                      </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              Download/Request 
                          </div>
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="download1" class="form-check-label ">
                                  <input type="radio" name="student_radio_download" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="download2" class="form-check-label ">
                                  <input type="radio" name="student_radio_download" checked value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              Add
                          </div>
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="add1" class="form-check-label ">
                                  <input type="radio" name="student_radio_add" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="add2" class="form-check-label">
                                  <input type="radio" name="student_radio_add" checked value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
                  </div>
                  <div class="row mt-2">
                      <div class="col-md-8">Catalog Management</div> 
                      <div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[]" value="Catalog_Management" type="checkbox" class="form-check-input" checked>
                          <label class="form-check-label" for="exampleCheck1">Yes</label>
                          </div>

                      </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              View 
                          </div>
                      </div> 
                      <div class="col-md-4">
                      <div class="form-check-inline form-check">
                              <label for="catalog1" class="form-check-label ">
                                  <input type="radio" id="catalog1" name="catalog_radio_view" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="catalog2" class="form-check-label ">
                                  <input type="radio" id="catalog2" name="catalog_radio_view" checked value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              Download/Request 
                          </div>
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="download1" class="form-check-label ">
                                  <input type="radio" name="catalog_radio_download" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="download2" class="form-check-label ">
                                  <input type="radio" name="catalog_radio_download" checked value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              Add
                          </div>
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="add1" class="form-check-label ">
                                  <input type="radio" name="catalog_radio_add" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="add2" class="form-check-label">
                                  <input type="radio" name="catalog_radio_add" checked value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
                  </div>
                  <div class="row mt-2">
                      <div class="col-md-8">Hosted Site Management</div> 
                      <div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[]" value="Hosted_Site_Management" type="checkbox" class="form-check-input" checked>
                              <label class="form-check-label" for="exampleCheck1">Yes</label>
                          </div>
                      </div> 
                  </div>
                  <div class="row mt-2">
                      <div class="col-md-8">
                          Marketing Access 
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[]" value="Marketing_Access" type="checkbox" class="form-check-input" checked id="exampleCheck1">
                              <label class="form-check-label" for="exampleCheck1">Yes</label>
                          </div>
                      </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              On Demand Collateral  
                          </div> 
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="request1" class="form-check-label ">
                                  <input type="radio" name="marketing_radio_demand" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="request2" class="form-check-label ">
                                  <input type="radio" name="marketing_radio_demand" checked value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
                  </div>
                  <div class="row">
                      <div class="col-md-8">
                          <div class="ml-4">
                              Request Collateral 
                          </div> 
                      </div> 
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="request1" class="form-check-label ">
                                  <input type="radio" name="marketing_radio_request" value="Yes" class="form-check-input">Yes 
                              </label>
                              <label for="request2" class="form-check-label ">
                                  <input type="radio" name="marketing_radio_request" checked value="No" class="form-check-input">No
                              </label>
                          </div>
                      </div> 
                  </div>

              </div>
          </div> 
      </div>
</div>
@else
<div class="row" id="registrationaccount">
  <div class="col-md-8 mx-auto">
                                                <div class="card">
                                                    <div class="card-header"> Features and Access</div>
                                                    <div class="card-body card-block m-3 featuresaccess">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <p>Home Dashboard</p>
                                                                <p>Stats</p>
                                                                <p class="mt-2">Student Management</p>
                                                                <p class="ml-4">View</p>
                                                                <p class="ml-4">Download/Request</p>
                                                                <p class="mt-2">Catalog  Management</p>
                                                                <p class="ml-4">Download Full Catelog</p>
                                                                <p class="mt-2">Marketing Access</p>
                                                                <p class="ml-4">On Demand Collateral</p>
                                                            </div> 
                                                        </div>
                                                    </div>
                                                </div> 
                                            </div>
</div>
@endif
<div class="card-footer">
    <div class="row">
        <div class="col-md-12">
            <a href="/users" class="btn btn-secondary btn-sm">Back to Partner Users List</a>
            <button type="submit" class="btn btn-primary btn-sm  float-right">Save </button>                                                
        </div>
    </div>                                  

</div>