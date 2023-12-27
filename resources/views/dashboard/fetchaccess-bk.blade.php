<div class="row" id="fullaccess">
  <div class="col-md-12 mx-auto">
      <div class="card">
          <div class="card-header"> Features and Access</div>
          <div class="card-body card-block m-3 featuresaccess">
              @foreach($all_user_features as $feature_key => $access)
                  <div class="row mt-2" id="{{$feature_key}}">
                      <div class="col-md-8">{{$access['label']}}</div>
                      {{--<div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[{{$feature_key}}][parent]" value="{{$feature_key}}" type="checkbox" class="form-check-input" {{$access['is_checked'] ? 'checked':''}}>
                              <label class="form-check-label" for="fview1">Yes</label>
                          </div>
                      </div>--}}
                  </div>
                    @if(isset($access['sub_menu']))
                        @foreach($access['sub_menu'] as $option_key => $option)

                  <div class="row {{$option['id']}}">
                      <div class="col-md-8">
                          <div class="ml-4">
                              {{$option['label']}}
                          </div>
                      </div>
                      <div class="col-md-4">
                          <div class="form-check-inline form-check">
                              <label for="{{$option['id']}}" class="form-check-label ">
                                  <input name="feature[{{$feature_key}}][opt][{{$option_key}}]" type="radio" value="yes" class="form-check-input" {{$option['checked'] == \App\Models\UserAccess::ACCESS_TYPE_FULL ? 'checked="checked"':''}}>Full Access
                              </label>
                              <label for="{{$option['id']}}" class="form-check-label ">
                                  <input name="feature[{{$feature_key}}][opt][{{$option_key}}]" type="radio" value="no" class="form-check-input" {{!$option['checked'] == \App\Models\UserAccess::ACCESS_TYPE_READ_ONLY ? 'checked="checked"':''}}>Read Only
                              </label>
                          </div>
                      </div>
                  </div>
                      @endforeach
                  @endif
              @endforeach

              {{--<div class="row">
                      <div class="col-md-8">
                      Stats 
                      </div> 
                      <div class="col-md-4">
                      <div class="form-check">
                          <input name="feature[Stats]" value="Stats" type="checkbox" class="form-check-input" checked id="fstate">
                          <label class="form-check-label" for="fstate">Yes</label>
                          </div>
                      </div> 
              </div>
              <div class="row mt-2">
                      <div class="col-md-8">Student Management</div> 
                      <div class="col-md-4">
                          <div class="form-check">
                              <input name="feature[Student_Management]" value="Student_Management" type="checkbox" class="form-check-input" checked>
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
                                  <input name="feature[Student_Management][view]" type="radio" checked value="Yes" class="form-check-input">Yes
                              </label>
                              <label for="student2" class="form-check-label ">
                                  <input name="feature[Student_Management][view]" type="radio" value="No" class="form-check-input">No
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
                              <input name="feature[Student_Management][download]" type="radio" checked value="Yes" class="form-check-input">Yes
                          </label>
                          <label for="download2" class="form-check-label ">
                              <input name="feature[Student_Management][download]" type="radio" value="No" class="form-check-input">No
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
                              <input name="feature[Student_Management][add]" type="radio" checked value="Yes" class="form-check-input">Yes
                          </label>
                          <label for="add2" class="form-check-label">
                              <input name="feature[Student_Management][add]" type="radio" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>
              <div class="row mt-2">
                  <div class="col-md-8">Catalog Management</div> 
                  <div class="col-md-4">
                      <div class="form-check">
                          <input name="feature[Catalog_Management]" value="Catalog_Management" type="checkbox" class="form-check-input" checked>
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
                              <input type="radio" name="feature[Catalog_Management][view]" checked value="Yes" class="form-check-input">Yes
                          </label>
                          <label for="catalog2" class="form-check-label ">
                              <input type="radio" name="feature[Catalog_Management][view]" value="No" class="form-check-input">No
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
                              <input type="radio" name="feature[Catalog_Management][download]" checked value="Yes" class="form-check-input">Yes
                          </label>
                          <label for="download2" class="form-check-label ">
                              <input type="radio" name="feature[Catalog_Management][download]" value="No" class="form-check-input">No
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
                              <input type="radio" name="feature[Catalog_Management][add]" checked value="Yes" class="form-check-input">Yes
                          </label>
                          <label for="fadd2" class="form-check-label">
                              <input type="radio" name="feature[Catalog_Management][add]" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>
              <div class="row mt-2">
                  <div class="col-md-8">Hosted Site Management</div> 
                  <div class="col-md-4">
                      <div class="form-check">
                          <input name="feature[Hosted_Site_Management]" value="Hosted_Site_Management" type="checkbox" class="form-check-input" checked>
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
                          <input name="feature[Marketing_Access]" value="Marketing_Access" type="checkbox" class="form-check-input" checked>
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
                              <input type="radio" name="feature[Marketing_Access][demand]" checked value="Yes" class="form-check-input">Yes
                          </label>
                          <label for="catalog2" class="form-check-label ">
                              <input type="radio" name="feature[Marketing_Access][demand]" value="No" class="form-check-input">No
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
                              <input type="radio" name="feature[Request_Collateral][request]" checked value="Yes" class="form-check-input">Yes
                          </label>
                          <label for="request2" class="form-check-label ">
                              <input type="radio" name="feature[Request_Collateral][request]" value="No" class="form-check-input">No
                          </label>
                      </div>
                  </div> 
              </div>--}}

          </div>
      </div> 
  </div>
</div>

<div class="card-footer">
    <div class="row">
        <div class="col-md-12">
            <a href="/dashboard/partnerusers" class="btn btn-secondary btn-sm">Back to Partner Users List</a>
            <button type="submit" class="btn btn-primary btn-sm  float-right">Save </button>                                                
        </div>
    </div>                                  

</div>